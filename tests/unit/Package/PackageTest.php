<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use PHPUnit\Framework\TestCase;

/**
 * Exercises the Package entity.
 *
 * @package Codehulk\Package
 */
class PackageTest extends TestCase
{
    /**
     * Tests that a simple package can be created.
     */
    public function testCreation()
    {
        $id = 'Test';
        $paths = [__DIR__];

        $namespace = $this->createMock(NamespaceInterface::class);
        $namespace->method('getId')
                  ->willReturn($id);
        $namespace->method('getPaths')
                  ->willReturn($paths);

        $package = new Package($namespace);

        $this->assertSame($id, $package->getId());
        $this->assertSame($paths, $package->getPaths());
    }

    /**
     * Given no parent, a package should act as if it's a root package.
     */
    public function testCreationWithoutParent()
    {
        $package = new Package(
            $this->createMock(NamespaceInterface::class)
        );
        $this->assertFalse($package->isSubPackage());
        $this->assertNull($package->getParent());
    }

    /**
     * Given a parent, a package should appear as a sub-package.
     */
    public function testCreationWithParent()
    {
        $parent = $this->createMock(Package::class);
        $package = new Package(
            $this->createMock(NamespaceInterface::class),
            $parent
        );
        $this->assertTrue($package->isSubPackage());
        $this->assertSame($parent, $package->getParent());
    }
}
