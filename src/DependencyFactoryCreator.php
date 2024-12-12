<?php declare(strict_types = 1);

namespace Nettrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ConnectionRegistryConnection;
use Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\Persistence\ConnectionRegistry;
use Doctrine\Persistence\ManagerRegistry;
use Nette\DI\Container;
use Nettrine\Migrations\Exceptions\LogicalException;
use Nettrine\Migrations\Migration\MigrationFactoryDecorator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class DependencyFactoryCreator
{

	public static function create(
		Container $container,
		Configuration $configuration,
		?ConnectionRegistry $connectionRegistry = null,
		?ManagerRegistry $managerRegistry = null,
		?LoggerInterface $logger = null
	): DependencyFactory
	{
		$logger ??= new NullLogger();

		if ($managerRegistry !== null) {
			$dependencyFactory = DependencyFactory::fromEntityManager(
				new ExistingConfiguration($configuration),
				ManagerRegistryEntityManager::withSimpleDefault($managerRegistry),
				$logger
			);
		} elseif ($connectionRegistry !== null) {
			$dependencyFactory = DependencyFactory::fromConnection(
				new ExistingConfiguration($configuration),
				ConnectionRegistryConnection::withSimpleDefault($connectionRegistry),
				$logger
			);
		} else {
			throw new LogicalException('You must provide either ManagerRegistry or ConnectionRegistry.');
		}

		$migrationFactory = new class ($dependencyFactory) implements MigrationFactory {

			public function __construct(
				private DependencyFactory $dependencyFactory
			)
			{
			}

			public function createVersion(string $migrationClassName): AbstractMigration
			{
				$migration = new $migrationClassName(
					$this->dependencyFactory->getConnection(),
					$this->dependencyFactory->getLogger()
				);

				assert($migration instanceof AbstractMigration);

				return $migration;
			}

		};

		$dependencyFactory->setService(MigrationFactory::class, new MigrationFactoryDecorator($container, $migrationFactory));

		return $dependencyFactory;
	}

}
