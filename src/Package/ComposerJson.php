<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use Generator;
use InvalidArgumentException;

/**
 * An interface to parse namespaces from a composer.json file.
 *
 * @package Codehulk\Package
 * @api
 */
class ComposerJson
{
    /** @var NamespaceInterface[] The namespaces declared in the composer.json file. */
    private $namespaces;

    /**
     * Constructor.
     *
     * @param string $jsonPath A path to a composer.json file.
     */
    public function __construct(string $jsonPath)
    {
        $path = realpath($jsonPath);
        if (!$path) {
            throw new InvalidArgumentException('');
        }
        $this->namespaces = $this->parseFile($path);
    }

    /**
     * Parses namespaces from a composer.json file.
     *
     * @param string $path A path to a composer.json file.
     *
     * @return NamespaceInterface[]
     */
    private function parseFile(string $path): array
    {
        $json = json_decode(file_get_contents($path), true);
        $root = dirname($path);

        $namespaces = array_merge(
            iterator_to_array($this->parseNamespaces($json['autoload']['psr-4'] ?: [], $root)),
            iterator_to_array($this->parseNamespaces($json['autoload-dev']['psr-4'] ?: [], $root))
        );
        return $namespaces;
    }

    /**
     * Parses namespaces from a configuration section.
     *
     * @param array  $config A namespace configuration from composer.json.
     * @param string $root   The root path to judge relative paths from.
     *
     * @return Generator|NamespaceInterface[]
     */
    private function parseNamespaces(array $config, string $root): Generator
    {
        foreach ($config as $name => $paths) {
            $paths = is_array($paths) ? $paths : [$paths];
            foreach ($paths as $path) {
                $path = realpath($root . '/' . $path);
                if (!$path) {
                    continue;
                }
                yield new Psr4Namespace($name, $path);
            }
        }
    }

    /**
     * Gets the namespaces in the composer.json file.
     *
     * @return NamespaceInterface[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }
}
