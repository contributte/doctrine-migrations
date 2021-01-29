<?php declare(strict_types = 1);

namespace Nettrine\Migrations\Command;

use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixedExecuteCommand extends ExecuteCommand
{

	public function getName(): string
	{
		return self::$defaultName;
	}

	public function execute(InputInterface $input, OutputInterface $output): ?int
	{
		$version = $input->getArgument('version');
		assert(is_string($version));
		$this->configuration->getVersion($version);

		return parent::execute($input, $output);
	}

}
