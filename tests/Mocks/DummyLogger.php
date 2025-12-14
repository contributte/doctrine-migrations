<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * A custom logger for testing.
 */
final class DummyLogger extends AbstractLogger
{

	/** @var array<array{level: string, message: string, context: mixed[]}> */
	public array $logs = [];

	/**
	 * @param mixed[] $context
	 */
	public function log(mixed $level, string|Stringable $message, array $context = []): void
	{
		$this->logs[] = [
			'level' => (string) $level,
			'message' => (string) $message,
			'context' => $context,
		];
	}

}
