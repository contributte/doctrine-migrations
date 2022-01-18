<?php declare(strict_types = 1);

namespace Nettrine\Migrations\DI;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory as MigrationsDependencyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Nette\DI\ServiceCreationException;
use Psr\Log\LoggerInterface;

class DependencyFactory
{

	/** @var Configuration */
	private $configuration;

	/** @var Connection|null */
	private $connection;

	/** @var EntityManagerInterface|null */
	private $entityManager;

	/** @var LoggerInterface|null */
	private $logger;

	public function __construct(Configuration $configuration, ?Connection $connection = null, ?EntityManagerInterface $entityManager = null, ?LoggerInterface $logger = null)
	{
		$this->configuration = $configuration;
		$this->connection = $connection;
		$this->entityManager = $entityManager;
		$this->logger = $logger;
	}

	public function createDependencyFactory(): MigrationsDependencyFactory
	{
		if ($this->entityManager !== null) {
			return MigrationsDependencyFactory::fromEntityManager(new ExistingConfiguration($this->configuration), new ExistingEntityManager($this->entityManager), $this->logger);
		}

		if ($this->connection !== null) {
			return MigrationsDependencyFactory::fromConnection(new ExistingConfiguration($this->configuration), new ExistingConnection($this->connection), $this->logger);
		}

		throw new ServiceCreationException(
			sprintf(
				'Either service of type %s or %s needs to be registered for Doctrine migrations to work properly',
				Connection::class,
				EntityManagerInterface::class
			)
		);
	}

}
