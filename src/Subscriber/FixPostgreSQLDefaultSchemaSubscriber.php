<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

final class FixPostgreSQLDefaultSchemaSubscriber implements EventSubscriber
{

	/**
	 * @return string[]
	 */
	public function getSubscribedEvents(): array
	{
		return [ToolEvents::postGenerateSchema];
	}

	public function postGenerateSchema(GenerateSchemaEventArgs $args): void
	{
		$schemaManager = $args
			->getEntityManager()
			->getConnection()
			->createSchemaManager();

		if (!$schemaManager instanceof PostgreSQLSchemaManager) {
			return;
		}

		foreach ($schemaManager->getExistingSchemaSearchPaths() as $namespace) {
			if (!$args->getSchema()->hasNamespace($namespace)) {
				$args->getSchema()->createNamespace($namespace);
			}
		}
	}

}
