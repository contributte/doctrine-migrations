<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Version;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Nette\DI\Container;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DbalMigrationFactory implements MigrationFactory
{

	private Container $container;

	private Connection $connection;

	private LoggerInterface $logger;

	public function __construct(Container $container, Connection $connection, ?LoggerInterface $logger = null)
	{
		$this->container = $container;
		$this->connection = $connection;
		$this->logger = $logger ?? new NullLogger();
	}

	public function createVersion(string $migrationClassName): AbstractMigration
	{
		$migration = new $migrationClassName(
			$this->connection,
			$this->logger
		);

		assert($migration instanceof AbstractMigration);

		$this->container->callInjects($migration);

		return $migration;
	}

}
