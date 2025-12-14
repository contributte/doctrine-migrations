<?php declare(strict_types = 1);

namespace Tests\Mocks;

use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;

/**
 * A custom comparator for testing that compares migrations by class name only,
 * ignoring the namespace (useful for ordering migrations from different namespaces).
 */
final class DummyComparator implements Comparator
{

	public function compare(Version $a, Version $b): int
	{
		// Extract class name without namespace
		$classA = $this->getClassName((string) $a);
		$classB = $this->getClassName((string) $b);

		return strcmp($classA, $classB);
	}

	private function getClassName(string $fullyQualifiedName): string
	{
		$parts = explode('\\', $fullyQualifiedName);

		return end($parts);
	}

}
