<?php declare(strict_types = 1);

namespace Nettrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ConnectionRegistryConnection;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
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
			$connections = $container->findByType(Connection::class);

			if ($connections === []) {
				throw new LogicalException('You must provide either ManagerRegistry, ConnectionRegistry or Connection.');
			}

			if (count($connections) > 1) {
				throw new LogicalException('Multiple DBAL connections found, provide ConnectionRegistry or ManagerRegistry (e.g. nettrine/orm).');
			}

			if ($configuration->getConnectionName() !== null) {
				throw new LogicalException('Named connection requires ConnectionRegistry or ManagerRegistry (e.g. nettrine/orm).');
			}

			$connection = $container->getService($connections[0]);
			assert($connection instanceof Connection);

			$dependencyFactory = DependencyFactory::fromConnection(
				new ExistingConfiguration($configuration),
				new ExistingConnection($connection),
				$logger
			);
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
