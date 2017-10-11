<?php
declare(strict_types = 1);

namespace Codehulk\Package;

/**
 * Exercises the composer.json parser.
 *
 * @package Codehulk\Package
 */
class ComposerJsonTest extends TestCase
{
    /**
     * Given a composer.json file, the namespaces should successfully be parsed.
     */
    public function testParsing()
    {
        $composer = new ComposerJson($this->fixturePath('composer.json'));

        /** @var NamespaceInterface[] $result */
        $result = $composer->getNamespaces();
        $this->assertCount(3, $result);

        $this->assertInstanceOf(NamespaceInterface::class, $result[0]);
        $this->assertSame('Test\\Package1', $result[0]->getId());

        $this->assertInstanceOf(NamespaceInterface::class, $result[1]);
        $this->assertSame('Test\\Package3', $result[1]->getId());

        $this->assertInstanceOf(NamespaceInterface::class, $result[2]);
        $this->assertSame('Test', $result[2]->getId());
    }

    /**
     * Given an invalid file, we should be unable to create a class.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidFile()
    {
        new ComposerJson($this->fixturePath('') . '/not-a-real-composer.json');
    }
}
