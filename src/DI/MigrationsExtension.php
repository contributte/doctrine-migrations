<?php

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
		'versionsOrganization' => NULL,
	];

	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);
		$config = Helpers::expand($config, $builder->parameters);

		// Register configuration
		$configuration = $builder->addDefinition($this->prefix('configuration'));
		$configuration
			->setClass(ContainerAwareConfiguration::class)
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
			->setClass(DiffCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('executeCommand'))
			->setClass(ExecuteCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('generateCommand'))
			->setClass(GenerateCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('latestCommand'))
			->setClass(LatestCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('migrateCommand'))
			->setClass(MigrateCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('statusCommand'))
			->setClass(StatusCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('upToDateCommand'))
			->setClass(UpToDateCommand::class)
			->setAutowired(FALSE);
		$builder->addDefinition($this->prefix('versionCommand'))
			->setClass(VersionCommand::class)
			->setAutowired(FALSE);

		// Register configuration helper
		$builder->addDefinition($this->prefix('configurationHelper'))
			->setClass(ConfigurationHelper::class)
			->setAutowired(FALSE);
	}

	/**
	 * Decorate services
	 *
	 * @return void
	 */
	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		// Register console helper only if console is provided
		$application = $builder->getByType(Application::class, FALSE);
		if ($application) {
			$applicationDef = $builder->getDefinition($application);
			$applicationDef->addSetup(
				new Statement('$service->getHelperSet()->set(?)', ['@' . $this->prefix('configurationHelper')])
			);
		}
	}

}
