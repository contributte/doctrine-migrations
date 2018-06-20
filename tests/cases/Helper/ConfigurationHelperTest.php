<?php declare(strict_types = 1);

namespace Tests\Nettrine\Migrations\Helper;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Mockery;
use Mockery\MockInterface;
use Nettrine\Migrations\Helper\ConfigurationHelper;
use Symfony\Component\Console\Input\InputInterface;
use Tests\Nettrine\Migrations\TestCase;

final class ConfigurationHelperTest extends TestCase
{

	public function testGetMigrationConfig(): void
	{
		/** @var InputInterface|MockInterface $input */
		$input = Mockery::mock(InputInterface::class);
		/** @var OutputWriter|MockInterface $outputWriter */
		$outputWriter = Mockery::mock(OutputWriter::class);

		$helper = new ConfigurationHelper();
		self::assertNull($helper->getMigrationConfig($input, $outputWriter));

		/** @var Configuration|MockInterface $configuration */
		$configuration = Mockery::mock(Configuration::class)
			->shouldReceive('setOutputWriter')
			->withArgs([$outputWriter])
			->getMock();

		$helper = new ConfigurationHelper(null, $configuration);
		self::assertSame($configuration, $helper->getMigrationConfig($input, $outputWriter));
	}

}
