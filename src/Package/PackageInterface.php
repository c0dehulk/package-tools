<?php
declare(strict_types = 1);

namespace Codehulk\Package;

/**
 * Describes a package.
 *
 * A package is a collection of related classes that forms a single unit of functionality. Much like a class is a
 *  collection of interdependent functions and state, a package is a collection of interdependent classes, and by
 *  definition, state as well.
 *
 * @package Codehulk\Package
 * @api
 */
interface PackageInterface extends NamespaceInterface
{
    /**
     * Determines whether this is a sub-package or not.
     *
     * @return bool
     */
    public function isSubPackage(): bool;

    /**
     * Gets this package's parent.
     *
     * @return PackageInterface|null The parent package, or null if this is not a sub-package.
     */
    public function getParent();
}
