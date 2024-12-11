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

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				parameters:
					appDir: /root
				migrations:
					directories:
						App\Domain: %appDir%/migrations
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
			'));
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::equal(['App\\Domain' => '/root/migrations'], $configuration->getMigrationDirectories());

	// Console
	Assert::count(13, $container->findByType(DoctrineCommand::class));
	Assert::count(4, iterator_to_array($container->getByType(Application::class)->getHelperSet()));

	/** @var DependencyFactory $dependencyFactory */
	$dependencyFactory = $container->getByType(DependencyFactory::class);
	Assert::type(DependencyFactory::class, $dependencyFactory);
	Assert::type(DbalMigrationFactory::class, $dependencyFactory->getMigrationFactory());
});
