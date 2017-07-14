<?php

namespace Nettrine\Migrations\DI;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand;
use Nette\DI\CompilerExtension;
use Symfony\Component\Console\Application;

final class MigrationsExtension extends CompilerExtension
{

	/**
	 * @var string[]
	 */
	private $defaults = [
		'table' => 'doctrine_migrations',
		'column' => 'version',
		'directory' => '%appDir%/../migrations',
		'namespace' => 'Migrations',
		'versionsOrganization' => NULL,
	];

	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		//Register commands
		$builder->addDefinition($this->prefix('diffCommand'))
			->setClass(DiffCommand::class);
		$builder->addDefinition($this->prefix('executeCommand'))
			->setClass(ExecuteCommand::class);
		$builder->addDefinition($this->prefix('generateCommand'))
			->setClass(GenerateCommand::class);
		$builder->addDefinition($this->prefix('latestCommand'))
			->setClass(LatestCommand::class);
		$builder->addDefinition($this->prefix('migrateCommand'))
			->setClass(MigrateCommand::class);
		$builder->addDefinition($this->prefix('statusCommand'))
			->setClass(StatusCommand::class);
		$builder->addDefinition($this->prefix('upToDateCommand'))
			->setClass(UpToDateCommand::class);
		$builder->addDefinition($this->prefix('versionCommand'))
			->setClass(VersionCommand::class);

		$config = $this->getConfig($this->defaults);
		$this->validateConfig($config);
		$config['directory'] = $this->getContainerBuilder()->expand($config['directory']);

		//Register configuration
		$configuration = $builder->addDefinition($this->prefix('configuration'));
		$configuration
			->setClass(Configuration::class)
			->addSetup('setMigrationsTableName', [$config['table']])
			->addSetup('setMigrationsColumnName', [$config['column']])
			->addSetup('setMigrationsDirectory', [$config['directory']])
			->addSetup('setMigrationsNamespace', [$config['namespace']]);

		if ($config['versionsOrganization'] === Configuration::VERSIONS_ORGANIZATION_BY_YEAR) {
			$configuration->addSetup('setMigrationsAreOrganizedByYear');
		} elseif ($config['versionsOrganization'] === Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH) {
			$configuration->addSetup('setMigrationsAreOrganizedByYearAndMonth');
		}
	}

	/**
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$configuration = $builder->getDefinition($builder->getByType(Configuration::class));
		foreach ($builder->findByType(AbstractCommand::class) as $command) {
			$command->addSetup('setMigrationConfiguration', [$configuration]);
		}
		$application = $builder->getDefinition($builder->getByType(Application::class));
		foreach ($builder->findByType(AbstractCommand::class) as $command) {
			$application->addSetup('add', [$command]);
		}
	}

}
