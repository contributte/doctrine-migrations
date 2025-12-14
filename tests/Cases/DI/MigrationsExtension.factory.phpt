<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Migrations\DependencyFactory;
use Nette\DI\Compiler;
use Nettrine\Migrations\DI\MigrationsExtension;
use Tester\Assert;
use Tests\Mocks\DummyMigrationFactory;

require_once __DIR__ . '/../../bootstrap.php';

// Custom migration factory
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
					migrationFactory: Tests\Mocks\DummyMigrationFactory
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
					- Psr\Log\NullLogger
			'));
		})
		->build();

	/** @var DependencyFactory $dependencyFactory */
	$dependencyFactory = $container->getByType(DependencyFactory::class);
	Assert::type(DummyMigrationFactory::class, $dependencyFactory->getMigrationFactory());
});
