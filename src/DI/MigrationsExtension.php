<?php declare(strict_types = 1);

namespace Nettrine\Migrations\DI;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\MigrationFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nettrine\Migrations\DependencyFactoryCreator;
use Nettrine\Migrations\DI\Helpers\SmartStatement;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class MigrationsExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		$expectService = Expect::anyOf(
			Expect::string()->required()->assert(fn ($input) => str_starts_with($input, '@') || class_exists($input) || interface_exists($input)),
			Expect::type(Statement::class)->required(),
		);

		return Expect::structure([
			'table' => Expect::string('doctrine_migrations'),
			'column' => Expect::string('version'),
			'directories' => Expect::arrayOf(Expect::string(), Expect::string())->required(),
			'versionsOrganization' => Expect::anyOf(
				null,
				Configuration::VERSIONS_ORGANIZATION_BY_YEAR,
				Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH
			),
			'customTemplate' => Expect::string(),
			'allOrNothing' => Expect::bool(false),
			'logger' => (clone $expectService),
			'migrationFactory' => (clone $expectService),
			'comparator' => (clone $expectService),
			'connection' => Expect::string(),
			'manager' => Expect::string(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		// Register configuration
		$storage = $builder->addDefinition($this->prefix('configuration.tableStorage'));
		$storage
			->setFactory(TableMetadataStorageConfiguration::class)
			->addSetup('setTableName', [$config->table])
			->addSetup('setVersionColumnName', [$config->column]);

		$configuration = $builder->addDefinition($this->prefix('configuration'));
		$configuration
			->setFactory(Configuration::class)
			->addSetup('setCustomTemplate', [$config->customTemplate])
			->addSetup('setMetadataStorageConfiguration', [$storage])
			->addSetup('setAllOrNothing', [$config->allOrNothing]);

		if ($config->connection !== null) {
			$configuration->addSetup('setConnectionName', [$config->connection]);
		}

		if ($config->manager !== null) {
			$configuration->addSetup('setEntityManagerName', [$config->manager]);
		}

		foreach ($config->directories as $namespace => $directory) {
			$configuration->addSetup('addMigrationsDirectory', [$namespace, $directory]);
		}

		if ($config->versionsOrganization === Configuration::VERSIONS_ORGANIZATION_BY_YEAR) {
			$configuration->addSetup('setMigrationsAreOrganizedByYear');
		} elseif ($config->versionsOrganization === Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH) {
			$configuration->addSetup('setMigrationsAreOrganizedByYearAndMonth');
		}

		$dependencyFactory = $builder->addDefinition($this->prefix('dependencyFactory'))
			->setType(DependencyFactory::class)
			->setFactory(DependencyFactoryCreator::class . '::create');

		if ($config->migrationFactory !== null) {
			$dependencyFactory->addSetup('setService', [MigrationFactory::class, SmartStatement::from($config->migrationFactory)]);
		}

		if ($config->comparator !== null) {
			$dependencyFactory->addSetup('setService', [Comparator::class, SmartStatement::from($config->comparator)]);
		}

		// Register commands

		$builder->addDefinition($this->prefix('currentCommand'))
			->setFactory(CurrentCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:current');

		$builder->addDefinition($this->prefix('diffCommand'))
			->setFactory(DiffCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:diff');

		$builder->addDefinition($this->prefix('dumpSchemaCommand'))
			->setFactory(DumpSchemaCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:dump-schema');

		$builder->addDefinition($this->prefix('executeCommand'))
			->setFactory(ExecuteCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:execute');

		$builder->addDefinition($this->prefix('generateCommand'))
			->setFactory(GenerateCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:generate');

		$builder->addDefinition($this->prefix('latestCommand'))
			->setFactory(LatestCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:latest');

		$builder->addDefinition($this->prefix('listCommand'))
			->setFactory(ListCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:list');

		$builder->addDefinition($this->prefix('migrateCommand'))
			->setFactory(MigrateCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:migrate');

		$builder->addDefinition($this->prefix('rollupCommand'))
			->setFactory(RollupCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:rollup');

		$builder->addDefinition($this->prefix('statusCommand'))
			->setFactory(StatusCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:status');

		$builder->addDefinition($this->prefix('syncMetadataCommand'))
			->setFactory(SyncMetadataCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:sync-metadata-storage');

		$builder->addDefinition($this->prefix('upToDateCommand'))
			->setFactory(UpToDateCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:up-to-date');

		$builder->addDefinition($this->prefix('versionCommand'))
			->setFactory(VersionCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:version');
	}

}
