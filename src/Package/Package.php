<?php
declare(strict_types = 1);

namespace Codehulk\Package;

/**
 * A package.
 *
 * @package Codehulk\Package
 */
class Package extends Psr4Namespace implements PackageInterface
{
    /** @var PackageInterface|null This package's parent, or null if it isn't a sub-package. */
    private $parent;

    /**
     * Constructor.
     *
     * @param NamespaceInterface    $namespace The namespace that holds the package.
     * @param PackageInterface|null $parent    This package's parent, if it has one.
     */
    public function __construct(NamespaceInterface $namespace, PackageInterface $parent = null)
    {
        parent::__construct($namespace->getId(), ...$namespace->getPaths());
        $this->parent = $parent;
    }

    /**
     * @inheritdoc
     */
    public function isSubPackage(): bool
    {
        return !is_null($this->parent);
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }
}
