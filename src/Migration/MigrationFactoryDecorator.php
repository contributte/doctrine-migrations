<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Migration;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\MigrationFactory;
use Nette\DI\Container;

class MigrationFactoryDecorator implements MigrationFactory
{

	public function __construct(
		private Container $container,
		private MigrationFactory $migrationFactory,
	)
	{
	}

	public function createVersion(string $migrationClassName): AbstractMigration
	{
		$instance = $this->migrationFactory->createVersion($migrationClassName);

		// Call setContainer
		if ($instance instanceof ContainerAwareInterface) {
			$instance->setContainer($this->container);
		}

		// Allow to use inject<>
		$this->container->callInjects($instance);

		return $instance;
	}

}
