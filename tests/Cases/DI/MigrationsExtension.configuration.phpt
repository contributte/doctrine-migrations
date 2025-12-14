<?php declare(strict_types = 1);

namespace Tests\Cases\Unit\DI;

use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use Contributte\Tester\Utils\Neonkit;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Nette\DI\Compiler;
use Nettrine\Migrations\DI\MigrationsExtension;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Custom table and column name
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					table: custom_migrations_table
					column: migration_version
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

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);

	/** @var TableMetadataStorageConfiguration|null $storageConfig */
	$storageConfig = $configuration->getMetadataStorageConfiguration();
	Assert::type(TableMetadataStorageConfiguration::class, $storageConfig);
	Assert::same('custom_migrations_table', $storageConfig->getTableName());
	Assert::same('migration_version', $storageConfig->getVersionColumnName());
});

// Versions organization by year
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
					versionsOrganization: year
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
			'));
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::true($configuration->areMigrationsOrganizedByYear());
	Assert::false($configuration->areMigrationsOrganizedByYearAndMonth());
});

// Versions organization by year and month
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
					versionsOrganization: year_and_month
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
			'));
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::true($configuration->areMigrationsOrganizedByYearAndMonth());
});

// allOrNothing configuration
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Domain: /root/migrations
					allOrNothing: true
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
			'));
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	Assert::true($configuration->isAllOrNothing());
});

// Multiple migration directories
Toolkit::test(function (): void {
	$container = ContainerBuilder::of()
		->withCompiler(function (Compiler $compiler): void {
			$compiler->addExtension('migrations', new MigrationsExtension());
			$compiler->addConfig(Neonkit::load('
				migrations:
					directories:
						App\Migrations: /app/migrations
						App\CoreMigrations: /core/migrations
						Vendor\PackageMigrations: /vendor/package/migrations
				services:
					- Symfony\Component\Console\Application
					- Doctrine\DBAL\Driver\Mysqli\Driver
					- Doctrine\DBAL\Connection([])
					- Tests\Mocks\DummyConnectionRegistry
			'));
		})
		->build();

	/** @var Configuration $configuration */
	$configuration = $container->getByType(Configuration::class);
	$directories = $configuration->getMigrationDirectories();

	Assert::count(3, $directories);
	Assert::same('/app/migrations', $directories['App\\Migrations']);
	Assert::same('/core/migrations', $directories['App\\CoreMigrations']);
	Assert::same('/vendor/package/migrations', $directories['Vendor\\PackageMigrations']);
});
