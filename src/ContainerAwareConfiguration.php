<?php

namespace Nettrine\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
use Nette\DI\Container;

class ContainerAwareConfiguration extends Configuration
{

	/** @var Container */
	private $container;

	/**
	 * @param Container $container
	 * @return void
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param string $direction
	 * @param string $to
	 * @return array
	 */
	public function getMigrationsToExecute($direction, $to)
	{
		$versions = parent::getMigrationsToExecute($direction, $to);
		if ($this->container) {
			foreach ($versions as $version) {
				$this->container->callInjects($version->getMigration());
			}
		}
		return $versions;
	}

	/**
	 * @param string $version
	 * @return Version|string
	 */
	public function getVersion($version)
	{
		$version = parent::getVersion($version);

		if ($this->container)
			$this->container->callInjects($version->getMigration());

		return $version;
	}

}
