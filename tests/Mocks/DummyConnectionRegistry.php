<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ConnectionRegistry;

final class DummyConnectionRegistry implements ConnectionRegistry
{

	public function __construct(
		private Connection $connection
	)
	{
	}

	public function getDefaultConnectionName(): string
	{
		return 'default';
	}

	public function getConnection(?string $name = null): Connection
	{
		return $this->connection;
	}

	/**
	 * @return array<string, Connection>
	 */
	public function getConnections(): array
	{
		return ['default' => $this->connection];
	}

	/**
	 * @return string[]
	 */
	public function getConnectionNames(): array
	{
		return ['default'];
	}

}
