<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Nette\DI\Compiler;
use Nettrine\Migrations\DI\MigrationsExtension;
use Nettrine\Migrations\Version\DbalMigrationFactory;
use Symfony\Component\Console\Application;
use Tester\Assert;
use Tests\Toolkit\NeonLoader;

require_once __DIR__ . '/../../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				parameters:
					appDir: /root
				migrations:
					directory: %appDir%/migrations
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::equal(['Migrations' => '/root/migrations'], $configuration->getMigrationDirectories());
	Assert::count(13, $container->findByType(DoctrineCommand::class));
	// 4 default helpers
	Assert::count(4, iterator_to_array($container->getByType(Application::class)->getHelperSet()));
});

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directory: /fake/migrations
					namespace: Fake
				services:
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::equal(['Fake' => '/fake/migrations'], $configuration->getMigrationDirectories());
});

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directory: /root/migrations
				services:
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::equal(['Migrations' => '/root/migrations'], $configuration->getMigrationDirectories());
	Assert::count(13, $container->findByType(DoctrineCommand::class));
});

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				parameters:
					appDir: /root
				migrations:
					directory: %appDir%/migrations
				services:
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		})
		->build();

	/** @var DependencyFactory $dependencyFactory */
	$dependencyFactory = $container->getByType(DependencyFactory::class);
	Assert::type(DependencyFactory::class, $dependencyFactory);
	Assert::type(DbalMigrationFactory::class, $dependencyFactory->getMigrationFactory());
});
