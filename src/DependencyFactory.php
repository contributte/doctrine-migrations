<?php declare(strict_types = 1);

namespace Nettrine\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory as NativeDependencyFactory;
use Doctrine\Migrations\Version\MigrationFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nette\DI\ServiceCreationException;
use Nettrine\Migrations\Version\DbalMigrationFactory;
use Psr\Log\LoggerInterface;

class DependencyFactory
{

	public function __construct(
		private Configuration $configuration,
		private DbalMigrationFactory $migrationFactory,
		private ?Connection $connection = null,
		private ?EntityManagerInterface $entityManager = null,
		private ?LoggerInterface $logger = null
	)
	{
	}

	public function create(): NativeDependencyFactory
	{
		if ($this->entityManager !== null) {
			$dependencyFactory = NativeDependencyFactory::fromEntityManager(new ExistingConfiguration($this->configuration), new ExistingEntityManager($this->entityManager), $this->logger);
		} elseif ($this->connection !== null) {
			$dependencyFactory = NativeDependencyFactory::fromConnection(new ExistingConfiguration($this->configuration), new ExistingConnection($this->connection), $this->logger);
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
