<?php

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

	/**
	 * @param Connection|NULL $connection
	 * @param Configuration|NULL $configuration
	 */
	public function __construct(Connection $connection = NULL, Configuration $configuration = NULL)
	{
		parent::__construct($connection, $configuration);
		$this->configuration = $configuration;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputWriter $outputWriter
	 * @return Configuration|NULL
	 */
	public function getMigrationConfig(InputInterface $input, OutputWriter $outputWriter)
	{
		if ($this->configuration !== NULL) {
			$this->configuration->setOutputWriter($outputWriter);
		}
		return $this->configuration;
	}

}
