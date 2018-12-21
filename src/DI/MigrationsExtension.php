<?php declare(strict_types = 1);

namespace Nettrine\Migrations\DI;

use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use Nette\DI\CompilerExtension;
use Nette\DI\Helpers;
use Nette\DI\Statement;
use Nettrine\Migrations\ContainerAwareConfiguration;
use Nettrine\Migrations\Helper\ConfigurationHelper;
use Symfony\Component\Console\Application;

final class MigrationsExtension extends CompilerExtension
{

	/** @var mixed[] */
	private $defaults = [
		'table' => 'doctrine_migrations',
		'column' => 'version',
		'directory' => '%appDir%/../migrations',
		'namespace' => 'Migrations',
		'versionsOrganization' => null,
	];

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);
		$config = Helpers::expand($config, $builder->parameters);

		// Register configuration
		$configuration = $builder->addDefinition($this->prefix('configuration'));
		$configuration
			->setFactory(ContainerAwareConfiguration::class)
			->addSetup('setContainer', [new Statement('@container')])
			->addSetup('setMigrationsTableName', [$config['table']])
			->addSetup('setMigrationsColumnName', [$config['column']])
			->addSetup('setMigrationsDirectory', [$config['directory']])
			->addSetup('setMigrationsNamespace', [$config['namespace']]);

		if ($config['versionsOrganization'] === ContainerAwareConfiguration::VERSIONS_ORGANIZATION_BY_YEAR)
			$configuration->addSetup('setMigrationsAreOrganizedByYear');
		elseif ($config['versionsOrganization'] === ContainerAwareConfiguration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH)
			$configuration->addSetup('setMigrationsAreOrganizedByYearAndMonth');

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

		// Register configuration helper
		$builder->addDefinition($this->prefix('configurationHelper'))
			->setFactory(ConfigurationHelper::class)
			->setAutowired(false);
	}

	/**
	 * Decorate services
	 */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		// Register console helper only if console is provided
		$application = $builder->getByType(Application::class, false);
		if ($application !== null) {
			$applicationDef = $builder->getDefinition($application);
			$applicationDef->addSetup(
				new Statement('$service->getHelperSet()->set(?)', [$this->prefix('@configurationHelper')])
			);
		}
	}

}
