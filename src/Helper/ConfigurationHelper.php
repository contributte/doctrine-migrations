<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\ConfigurationHelper as BaseConfigurationHelper;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationHelper extends BaseConfigurationHelper
{

	/** @var Configuration|NULL */
	private $configuration;

	public function __construct(?Connection $connection = null, ?Configuration $configuration = null)
	{
		parent::__construct($connection, $configuration);
		$this->configuration = $configuration;
	}

	public function getMigrationConfig(InputInterface $input, OutputWriter $outputWriter): ?Configuration
	{
		if ($this->configuration !== null) {
			$this->configuration->setOutputWriter($outputWriter);
		}
		return $this->configuration;
	}

}
