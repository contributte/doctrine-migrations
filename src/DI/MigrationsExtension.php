<?php declare(strict_types = 1);

namespace Nettrine\Migrations\DI;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
final class MigrationsExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'table' => Expect::string('doctrine_migrations'),
			'column' => Expect::string('version'),
			'directory' => Expect::string()->required(),
			'namespace' => Expect::string('Migrations'),
			'versionsOrganization' => Expect::anyOf(
				null,
				Configuration::VERSIONS_ORGANIZATION_BY_YEAR,
				Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH
			),
			'customTemplate' => Expect::string(),
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
			->addSetup('addMigrationsDirectory', [$config->namespace, $config->directory]);

		if ($config->versionsOrganization === Configuration::VERSIONS_ORGANIZATION_BY_YEAR) {
			$configuration->addSetup('setMigrationsAreOrganizedByYear');
		} elseif ($config->versionsOrganization === Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH) {
			$configuration->addSetup('setMigrationsAreOrganizedByYearAndMonth');
		}

		$builder->addDefinition($this->prefix('nettrineDependencyFactory'))
			->setFactory(DependencyFactory::class)
			->setAutowired(false);

		$builder->addDefinition($this->prefix('dependencyFactory'))
			->setFactory($this->prefix('@nettrineDependencyFactory') . '::createDependencyFactory');

		// Register commands
		$builder->addDefinition($this->prefix('diffCommand'))
			->setFactory(DiffCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:diff');
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
		$builder->addDefinition($this->prefix('migrateCommand'))
			->setFactory(MigrateCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:migrate');
		$builder->addDefinition($this->prefix('statusCommand'))
			->setFactory(StatusCommand::class)
			->setAutowired(false)
			->addTag('console.command', 'migrations:status');
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
