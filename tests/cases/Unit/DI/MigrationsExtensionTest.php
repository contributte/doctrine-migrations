<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Migrations\DI\MigrationsExtension;
use Nettrine\Migrations\Version\DbalMigrationFactory;
use Symfony\Component\Console\Application;
use Tester\Assert;
use Tester\TestCase;
use Tests\Toolkit\NeonLoader;

require_once __DIR__ . '/../../../bootstrap.php';

final class MigrationsExtensionTest extends TestCase
{

	public function testDefault(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(NeonLoader::load('
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
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		/** @var Configuration $configuration */
		$configuration = $container->getByType(Configuration::class);
		Assert::equal(['Migrations' => '/root/migrations'], $configuration->getMigrationDirectories());
		Assert::count(8, $container->findByType(DoctrineCommand::class));
		// 4 default helpers
		Assert::count(4, iterator_to_array($container->getByType(Application::class)->getHelperSet()));
	}

	public function testCustom(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(NeonLoader::load('
			migrations:
				directory: /fake/migrations
				namespace: Fake
			services:
				- Doctrine\DBAL\Driver\Mysqli\Driver
				- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		/** @var Configuration $configuration */
		$configuration = $container->getByType(Configuration::class);
		Assert::equal(['Fake' => '/fake/migrations'], $configuration->getMigrationDirectories());
	}

	public function testWithoutConsole(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(NeonLoader::load('
			migrations:
				directory: /root/migrations
			services:
				- Doctrine\DBAL\Driver\Mysqli\Driver
				- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		/** @var Configuration $configuration */
		$configuration = $container->getByType(Configuration::class);
		Assert::equal(['Migrations' => '/root/migrations'], $configuration->getMigrationDirectories());
		Assert::count(8, $container->findByType(DoctrineCommand::class));
	}

	public function testDependencyFactory(): void
	{
		$loader = new ContainerLoader(TMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(NeonLoader::load('
			parameters:
				appDir: /root
			migrations:
				directory: %appDir%/migrations
			services:
				- Doctrine\DBAL\Driver\Mysqli\Driver
				- Doctrine\DBAL\Connection([])
			'));
			$compiler->addDependencies([__FILE__]);
		}, __METHOD__);

		/** @var Container $container */
		$container = new $class();

		/** @var DependencyFactory $dependencyFactory */
		$dependencyFactory = $container->getByType(DependencyFactory::class);
		Assert::type(DependencyFactory::class, $dependencyFactory);
		Assert::type(DbalMigrationFactory::class, $dependencyFactory->getMigrationFactory());
	}

}

(new MigrationsExtensionTest())->run();
