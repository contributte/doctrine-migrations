<?php declare(strict_types = 1);

namespace Nettrine\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Version;
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
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $direction
	 * @param string $to
	 * @return mixed[]
	 */
	public function getMigrationsToExecute($direction, $to): array
	{
		$versions = parent::getMigrationsToExecute($direction, $to);
		if ($this->container !== null) {
			foreach ($versions as $version) {
				$this->container->callInjects($version->getMigration());
			}
		}
		return $versions;
	}

	/**
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @param string $version
	 * @return Version|string
	 */
	public function getVersion($version)
	{
		$version = parent::getVersion($version);

		if ($this->container !== null)
			$this->container->callInjects($version->getMigration());

		return $version;
	}

}
