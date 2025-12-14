<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Psr\Log\LoggerInterface;

/**
 * A custom migration factory for testing.
 */
final class DummyMigrationFactory implements MigrationFactory
{

	public function __construct(
		private Connection $connection,
		private LoggerInterface $logger
	)
	{
	}

	public function createVersion(string $migrationClassName): AbstractMigration
	{
		return new $migrationClassName($this->connection, $this->logger);
	}

}
