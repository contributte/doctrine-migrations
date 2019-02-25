<?php declare(strict_types = 1);

namespace Nettrine\Migrations;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Version\Version;
use Nette\DI\Container;

class ContainerAwareConfiguration extends Configuration
{

	/** @var Container|null */
	private $container;

	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

	/**
	 * @return Version[]
	 */
	public function getMigrationsToExecute(string $direction, string $to): array
	{
		$versions = parent::getMigrationsToExecute($direction, $to);

		if ($this->container !== null) {
			foreach ($versions as $version) {
				$this->container->callInjects($version->getMigration());
			}
		}

		return $versions;
	}

	public function getVersion(string $version): Version
	{
		$version = parent::getVersion($version);

		if ($this->container !== null) {
			$this->container->callInjects($version->getMigration());
		}

		return $version;
	}

}
