<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Doctrine\Migrations\Tools\Console\Command\AbstractCommand;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nettrine\Migrations\ContainerAwareConfiguration;
use Nettrine\Migrations\DI\MigrationsExtension;
use Symfony\Component\Console\Application;
use Tests\Toolkit\NeonLoader;
use Tests\Toolkit\TestCase;

final class MigrationsExtensionTest extends TestCase
{

	public function testDefault(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
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

		/** @var ContainerAwareConfiguration $awareConfiguration */
		$awareConfiguration = $container->getByType(ContainerAwareConfiguration::class);
		$this->assertEquals('Migrations', $awareConfiguration->getMigrationsNamespace());
		$this->assertEquals('/root/migrations', $awareConfiguration->getMigrationsDirectory());
		$this->assertCount(8, $container->findByType(AbstractCommand::class));
		// 4 default helpers + configurationHelper
		$this->assertCount(5, iterator_to_array($container->getByType(Application::class)->getHelperSet()));
	}

	public function testCustom(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
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

		/** @var ContainerAwareConfiguration $awareConfiguration */
		$awareConfiguration = $container->getByType(ContainerAwareConfiguration::class);
		$this->assertEquals('Fake', $awareConfiguration->getMigrationsNamespace());
		$this->assertEquals('/fake/migrations', $awareConfiguration->getMigrationsDirectory());
	}

	public function testWithoutConsole(): void
	{
		$loader = new ContainerLoader(TEMP_PATH, true);
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

		/** @var ContainerAwareConfiguration $awareConfiguration */
		$awareConfiguration = $container->getByType(ContainerAwareConfiguration::class);
		$this->assertEquals('Migrations', $awareConfiguration->getMigrationsNamespace());
		$this->assertEquals('/root/migrations', $awareConfiguration->getMigrationsDirectory());
		$this->assertCount(8, $container->findByType(AbstractCommand::class));
	}

}
