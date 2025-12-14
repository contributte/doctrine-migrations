<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Version\AlphabeticalComparator;
use Nette\DI\Compiler;
use Nettrine\Migrations\DI\MigrationsExtension;
use Tester\Assert;
use Tests\Mocks\DummyComparator;

require_once __DIR__ . '/../../bootstrap.php';

// Custom comparator
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
					comparator: Tests\Mocks\DummyComparator
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
	Assert::type(DummyComparator::class, $dependencyFactory->getVersionComparator());
});

// Custom comparator via service reference
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
					comparator: @customComparator
				services:
					customComparator: Tests\Mocks\DummyComparator
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
			'));
		})
		->build();

	/** @var DependencyFactory $dependencyFactory */
	$dependencyFactory = $container->getByType(DependencyFactory::class);
	Assert::type(DummyComparator::class, $dependencyFactory->getVersionComparator());
});

// Default comparator (AlphabeticalComparator) when no custom comparator is configured
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
	Assert::type(AlphabeticalComparator::class, $dependencyFactory->getVersionComparator());
});
