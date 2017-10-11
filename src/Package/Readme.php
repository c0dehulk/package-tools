<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use Parsedown;

/**
 * A tool to extract readme documentation from a Package.
 *
 * @package Codehulk\Package
 * @api
 */
class Readme
{
    /** @var string|null The path to the package's readme file. */
    private $path;

    /**
     * Constructor.
     *
     * @param PackageInterface $package A package to search for documentation.
     *
     * @throws \Exception Thrown if multiple readme files are found in the package.
     */
    public function __construct(PackageInterface $package)
    {
        foreach ($package->getPaths() as $path) {
            $readmePath = realpath($path . '/readme.md') ?: null;
            if (!$readmePath) {
                continue;
            }
            if ($this->path) {
                throw new \Exception('Multiple readme files found in package.');
            }
            $this->path = $readmePath;
        }
    }

    /**
     * Determines if the readme file exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return ($this->path !== null);
    }

    /**
     * Gets the raw contents of the readme file.
     *
     * @return string
     */
    public function getContent(): string
    {
        if (!$this->exists()) {
            return '';
        }
        return file_get_contents($this->path);
    }

    /**
     * Gets the contents of the readme as HTML.
     *
     * @return string
     */
    public function getContentAsHtml(): string
    {
        $parser = new Parsedown();
        return $parser->parse($this->getContent());
    }
}
