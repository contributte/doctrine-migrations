<?php declare(strict_types = 1);

namespace Nettrine\Migrations\DI;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory as MigrationsDependencyFactory;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nette\DI\ServiceCreationException;
use Nettrine\Migrations\Version\DbalMigrationFactory;
use Psr\Log\LoggerInterface;

class DependencyFactory
{

	private Configuration $configuration;

	private ?Connection $connection = null;

	private ?EntityManagerInterface $entityManager = null;

	private ?LoggerInterface $logger = null;

	private DbalMigrationFactory $migrationFactory;

	public function __construct(Configuration $configuration, DbalMigrationFactory $migrationFactory, ?Connection $connection = null, ?EntityManagerInterface $entityManager = null, ?LoggerInterface $logger = null)
	{
		$this->configuration = $configuration;
		$this->connection = $connection;
		$this->entityManager = $entityManager;
		$this->logger = $logger;
		$this->migrationFactory = $migrationFactory;
	}

	public function createDependencyFactory(): MigrationsDependencyFactory
	{
		if ($this->entityManager !== null) {
			$dependencyFactory = MigrationsDependencyFactory::fromEntityManager(new ExistingConfiguration($this->configuration), new ExistingEntityManager($this->entityManager), $this->logger);
		} elseif ($this->connection !== null) {
			$dependencyFactory = MigrationsDependencyFactory::fromConnection(new ExistingConfiguration($this->configuration), new ExistingConnection($this->connection), $this->logger);
		} else {
			throw new ServiceCreationException(
				sprintf(
					'Either service of type %s or %s needs to be registered for Doctrine migrations to work properly',
					Connection::class,
					EntityManagerInterface::class
				)
			);
		}

		$dependencyFactory->setService(MigrationFactory::class, $this->migrationFactory);

		return $dependencyFactory;
	}

}
