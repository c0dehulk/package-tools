<?php
declare(strict_types = 1);

namespace Codehulk\Package;

/**
 * Common functionality for unit tests.
 *
 * @package Codehulk\Package
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Creates a path to a fixture.
     *
     * @param string $path The path within the fixtures folder.
     *
     * @return string
     */
    protected function fixturePath(string $path = ''): string
    {
        return realpath(__DIR__ . '/../../fixtures/' . trim($path, '\\/'));
    }
}
