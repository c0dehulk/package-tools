<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use Generator;
use InvalidArgumentException;

/**
 * A PSR-4 namespace.
 *
 * A namespace is an abstraction of a filesystem,.
 *
 * @package Codehulk\Package
 */
class Psr4Namespace implements NamespaceInterface
{
    /** @var string The identifier of the namespace. */
    private $id;

    /** @var string[] The absolute filesystem paths the namespace can be found in. */
    private $paths;

    /**
     * Constructor.
     *
     * @param string   $id       An identifier for the namespace, e.g. Codehulk\Name\Space.
     * @param string[] ...$paths A list of absolute filesystem paths where the namespace is stored.
     */
    public function __construct(string $id, string ...$paths)
    {
        if (!$this->isValidId($id)) {
            throw new InvalidArgumentException("Invalid namespace identifier: '{$id}'.");
        }
        $this->id = trim($id, '\\');

        $this->paths = [];
        foreach ($paths as $path) {
            $realPath = realpath($path);
            if (!$realPath) {
                throw new InvalidArgumentException("Invalid path: '{$path}'.");
            }
            $this->paths[] = $realPath;
        }
        $this->paths = array_unique($this->paths);
    }

    /**
     * Determines if an identifier is valid for a namespace.
     *
     * @param string $id A namespace identifier.
     *
     * @return bool
     */
    private function isValidId(string $id): bool
    {
        return !preg_match('/[^\w\\\\]/', $id);
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getParentId()
    {
        $lastPos = strrpos($this->id, '\\');
        if ($lastPos === false) {
            return null;
        }
        return substr($this->id, 0, $lastPos);
    }

    /**
     * @inheritdoc
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * @inheritdoc
     */
    public function findNamespace(string $id)
    {
        // If the path contains anything unexpected, we found nothing.
        if (!$this->isValidId($id)) {
            return null;
        }

        // If the requested namespace isn't inside this one, return nothing.
        $name = trim($id, '\\');
        if (strpos($name, $this->id) !== 0) {
            return null;
        }

        // Check if the requested path exists on the filesystem, taking into account the potentially multiple filesystem
        //  locations this namespace has.
        $relative = substr($name, strlen($this->id));
        $paths = [];
        foreach ($this->paths as $path) {
            $path = realpath($path . str_replace('\\', '/', $relative));
            if ($path) {
                $paths[] = $path;
            }
        }
        if (!$paths) {
            return null;
        }

        // Assuming we found something, return that as a new namespace.
        return new self($this->id . $relative, ...$paths);
    }

    /**
     * @inheritdoc
     */
    public function iterateNamespaces(): Generator
    {
        $folders = new \Symfony\Component\Finder\Finder();
        $folders->directories()
                ->in($this->paths)
                ->depth(0);

        // Iterate all descendant filesystem paths, associating each one with it's namespace identifier.
        $spaces = [];
        foreach ($folders as $folder) {
            $name = $folder->getRelativePathname();

            // If the name isn't legal, don't list it as a namespace.
            if (!$this->isValidId($name)) {
                continue;
            }

            // Store the name and path for later.
            if (!array_key_exists($name, $spaces)) {
                $spaces[$name] = [];
            }
            $spaces[$name][] = $folder->getRealPath();
        }
        ksort($spaces);

        // Emit child namespaces.
        foreach ($spaces as $id => $paths) {
            yield new self($this->id . '\\' . $id, ...$paths);
        }
    }
}
