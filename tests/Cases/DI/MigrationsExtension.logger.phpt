<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Migrations\DependencyFactory;
use Nette\DI\Compiler;
use Nettrine\Migrations\DI\MigrationsExtension;
use Psr\Log\NullLogger;
use Tester\Assert;
use Tests\Mocks\DummyLogger;

require_once __DIR__ . '/../../bootstrap.php';

// Custom logger
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
					- Tests\Mocks\DummyLogger
			'));
		})
		->build();

	/** @var DependencyFactory $dependencyFactory */
	$dependencyFactory = $container->getByType(DependencyFactory::class);
	Assert::type(DummyLogger::class, $dependencyFactory->getLogger());
});

// Default logger (NullLogger) when no custom logger is configured
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
			'));
		})
		->build();

	/** @var DependencyFactory $dependencyFactory */
	$dependencyFactory = $container->getByType(DependencyFactory::class);
	Assert::type(NullLogger::class, $dependencyFactory->getLogger());
});
