<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use Generator;

/**
 * Describes a PSR-4 namespace.
 *
 * @package Codehulk\Package
 * @api
 */
interface NamespaceInterface
{
    /**
     * Gets the identifier of the namespace.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Gets the identifier of this namespace's parent.
     *
     * @return string|null The parent's identifier, or null if this namespace is in the root.
     */
    public function getParentId();

    /**
     * Gets the absolute paths the namespace is based on..
     *
     * @return string[]
     */
    public function getPaths(): array;

    /**
     * Finds a descendant namespace by identifier.
     *
     * @param string $id A fully-qualified namespace identifier.
     *
     * @return self|null The namespace, or null if none found.
     */
    public function findNamespace(string $id);

    /**
     * Iterates all sub-namespaces in this namespace.
     *
     * @return Generator|self[]
     */
    public function iterateNamespaces(): Generator;
}
