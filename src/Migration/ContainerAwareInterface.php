<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Migration;

use Nette\DI\Container;

interface ContainerAwareInterface
{

	public function setContainer(Container $container): void;

}
