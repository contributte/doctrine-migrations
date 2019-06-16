<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Events;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
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
			->getSchemaManager();

		if (!$schemaManager instanceof PostgreSqlSchemaManager) {
			return;
		}

		foreach ($schemaManager->getExistingSchemaSearchPaths() as $namespace) {
			if (!$args->getSchema()->hasNamespace($namespace)) {
				$args->getSchema()->createNamespace($namespace);
			}
		}
	}

}
