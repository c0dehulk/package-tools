<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use Generator;
use IteratorAggregate;

/**
 * A tool to find packages in namespaces.
 *
 * @package Codehulk\Package
 * @api
 */
class Finder implements IteratorAggregate
{
    /** @var NamespaceInterface[] The array of namespaces to search. */
    private $namespaces;

    /** @var string[] The list of namespaces defined as package roots. */
    private $packageRoots;

    /**
     * Constructor.
     *
     * @param NamespaceInterface[] $namespaces   An array of namespaces to search.
     * @param string[]             $packageRoots A list of namespaces defined as package roots.
     */
    public function __construct(array $namespaces, array $packageRoots)
    {
        $this->namespaces = $namespaces;
        $this->packageRoots = $packageRoots;
    }

    /**
     * @inheritdoc
     *
     * @return Generator|PackageInterface[]
     */
    public function getIterator(): Generator
    {
        foreach ($this->packageRoots as $root) {
            $rootPaths = [];
            $packages = [];

            // Search each namespace we've been provided.
            foreach ($this->namespaces as $namespace) {

                // Try to find the root within this namespace.
                // If we find it, aggregate the paths to scan for packages later.
                $package = $namespace->findNamespace($root);
                if ($package) {
                    $rootPaths = array_merge($rootPaths, $package->getPaths());
                }

                // If the namespace itself is actually a package within the root we're searching for,
                // aggregate it's path to scan as a package for later.
                if ($namespace->getParentId() === $root) {
                    if (!array_key_exists($namespace->getId(), $packages)) {
                        $packages[$namespace->getId()] = [];
                    }
                    $packages[$namespace->getId()] = array_merge(
                        $packages[$namespace->getId()],
                        $namespace->getPaths()
                    );
                }
            }

            // If we found any root paths, scan them for packages.
            if ($rootPaths) {
                $namespace = new Psr4Namespace($root, ...$rootPaths);
                yield from $this->scanNamespace($namespace);
            }

            // If we found any package paths, scan them.
            foreach ($packages as $id => $paths) {
                $namespace = new Psr4Namespace($id, ...$paths);
                yield from $this->loadPackage($namespace);
            }
        }
    }

    /**
     * Finds all packages in a namespace.
     *
     * @param NamespaceInterface    $root   The namespace to search.
     * @param PackageInterface|null $parent The parent package currently being searched, or null if none.
     *
     * @return Generator|PackageInterface[]
     */
    private function scanNamespace(NamespaceInterface $root, PackageInterface $parent = null): Generator
    {
        foreach ($root->iterateNamespaces() as $namespace) {
            yield from $this->loadPackage($namespace, $parent);
        }
    }

    /**
     * Loads a package from a namespace.
     *
     * @param NamespaceInterface    $namespace The namespace to load the package with.
     * @param PackageInterface|null $parent    This package's parent, implying it is a sub-package. Null if none.
     *
     * @return Generator|PackageInterface[]
     */
    private function loadPackage(NamespaceInterface $namespace, PackageInterface $parent = null): Generator
    {
        $package = new Package($namespace, $parent);
        yield $package;

        // Packages can have a single tier of sub-packages. If we don't have a parent, search our namespace for any
        //  children we might have. If we have a parent, they caused enough psychological damage that we don't want kids.
        if (!$parent) {
            yield from $this->findSubPackages($package);
        }
    }

    /**
     * Finds all sub-packages in a package.
     *
     * @param PackageInterface $package The package to search.
     *
     * @return Generator|PackageInterface[]
     */
    private function findSubPackages(PackageInterface $package): Generator
    {
        $readMes = new \Symfony\Component\Finder\Finder();
        $readMes->files()
                ->in($package->getPaths())
                ->depth(1)
                ->name('readme.md');

        // If no sub-folders have a readme, there aren't any sub-packages here.
        if (!$readMes->count()) {
            return;
        }
        yield from $this->scanNamespace($package, $package);
    }
}
