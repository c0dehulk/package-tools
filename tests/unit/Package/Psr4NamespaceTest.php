<?php
declare(strict_types = 1);

namespace Codehulk\Package;

/**
 * Exercise the Psr4Namespace object.
 *
 * @package Codehulk\Package
 */
class Psr4NamespaceTest extends TestCase
{
    /**
     * Tests that a namespace can be created.
     */
    public function testCreation()
    {
        $namespace = new Psr4Namespace('A\\Namespace', $this->fixturePath());

        $this->assertSame('A\\Namespace', $namespace->getId());
        $this->assertSame([$this->fixturePath()], $namespace->getPaths());
    }

    /**
     * Given an invalid namespace identifier, we shouldn't be able to create a namespace.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testCreationWithInvalidId()
    {
        new Psr4Namespace('A\\Name-Space', $this->fixturePath());
    }

    /**
     * Given an absolute identifier for a namespace, this should be normalised without the leading \.
     */
    public function testIdNormalisation()
    {
        $namespace = new Psr4Namespace('\\An\\Absolute\\Namespace\\', $this->fixturePath());

        $this->assertSame('An\\Absolute\\Namespace', $namespace->getId());
    }

    /**
     * Given multiple paths, a namespace should rationalise and de-duplicate them.
     */
    public function testCreationWithMultiplePaths()
    {
        $paths = [
            $this->fixturePath(),
            $this->fixturePath() . '/root1/../root1',  // Real-path
            $this->fixturePath(), // De-duplication.
        ];
        $namespace = new Psr4Namespace('Id', ...$paths);

        $expected = [
            $this->fixturePath(),
            $this->fixturePath('root1'),
        ];
        $this->assertSame($expected, $namespace->getPaths());
    }

    /**
     * Given an invalid path, we shouldn't be able to create a namespace.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testCreationWithInvalidPath()
    {
        new Psr4Namespace('A\\Namespace', 'this/path/isnt/real');
    }

    /**
     * Given an invalid path amongst multiple valid ones, we shouldn't be able to create a namespace.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testCreationWithInvalidPaths()
    {
        new Psr4Namespace(
            'A\\Namespace',
            $this->fixturePath(),
            'this/path/isnt/real',
            __DIR__
        );
    }


    /**
     * Given a namespace, the parent should be correctly interpreted.
     *
     * @dataProvider dataGetParentId
     *
     * @param string $namespace A namespace.
     * @param string $expected  The expected parent.
     */
    public function testGetParentId($namespace, $expected)
    {
        $namespace = new Psr4Namespace($namespace, __DIR__);
        $this->assertSame($expected, $namespace->getParentId());
    }

    /**
     * Data provider for `testGetParentId()`.
     *
     * @return array
     */
    public function dataGetParentId(): array
    {
        return [
            ['Root', null],
            ['One\\Tier', 'One'],
            ['Two\\Tier\\s', 'Two\\Tier'],
            ['Lots\\Of\\Tiers\\Lots\\Of\\Tiers\\Lots\\Of\\Tiers', 'Lots\\Of\\Tiers\\Lots\\Of\\Tiers\\Lots\\Of'],
        ];
    }


    /**
     * When finding a namespace with illegal characters in it, nothing should be found.
     */
    public function testFindNamespaceIllegalCharacters()
    {
        $namespace = new Psr4Namespace('Test', $this->fixturePath('root1'));
        $this->assertNull($namespace->findNamespace('Test\\Illegal-Namespace'));
    }

    /**
     * When trying to find a namespace that isn't inside the namespace, nothing should be found.
     */
    public function testFindExternalNamespace()
    {
        $namespace = new Psr4Namespace('Test\\Package1', $this->fixturePath('root1/Package1'));
        $this->assertNull($namespace->findNamespace('Test\\Package3\\A'));
    }

    /**
     * When trying to find a non-existent namespace, nothing should be found.
     */
    public function testFindNonExistentNamespace()
    {
        $namespace = new Psr4Namespace('Test', $this->fixturePath('root1'));
        $this->assertNull($namespace->findNamespace('Test\\Package1\\C'));
    }

    /**
     * When trying to find a namespace that exists, we should find something.
     */
    public function testFindExistentNamespace()
    {
        $namespace = new Psr4Namespace('Test', $this->fixturePath('root1'));
        $result = $namespace->findNamespace('Test\\Package1\\A');

        $this->assertInstanceOf(NamespaceInterface::class, $result);
        $this->assertSame('Test\\Package1\\A', $result->getId());
        $this->assertSame(
            [$this->fixturePath('root1/Package1/A')],
            $result->getPaths()
        );
    }

    /**
     * When trying to find a namespace that exists in more than one path, we should find a multi-path namespace.
     */
    public function testFindMultiPathNamespace()
    {
        $namespace = new Psr4Namespace(
            'Test',
            $this->fixturePath('root1'),
            $this->fixturePath('root2')
        );
        $result = $namespace->findNamespace('Test\\Package3');

        $this->assertInstanceOf(NamespaceInterface::class, $result);
        $this->assertSame('Test\\Package3', $result->getId());
        $this->assertSame(
            [
                $this->fixturePath('root1/Package3'),
                $this->fixturePath('root2/Package3'),
            ],
            $result->getPaths()
        );
    }

    /**
     * When trying to find a namespace that exists in one path, but searching across both, a single-path namespace
     * should be found.
     */
    public function testFindSinglePathNamespaceAcrossMultiplePaths()
    {
        $namespace = new Psr4Namespace(
            'Test',
            $this->fixturePath('root1'),
            $this->fixturePath('root2')
        );
        $result = $namespace->findNamespace('Test\\Package2');

        $this->assertInstanceOf(NamespaceInterface::class, $result);
        $this->assertSame('Test\\Package2', $result->getId());
        $this->assertSame(
            [$this->fixturePath('root2/Package2')],
            $result->getPaths()
        );
    }


    /**
     * Given a namespace in a single path, iterating it's children should produce predictable results.
     */
    public function testIterateSinglePathNamespace()
    {
        $namespace = new Psr4Namespace('Test', $this->fixturePath('root1'));

        /** @var NamespaceInterface[] $result */
        $result = iterator_to_array($namespace->iterateNamespaces());
        $this->assertCount(2, $result);

        $this->assertInstanceOf(NamespaceInterface::class, $result[0]);
        $this->assertSame('Test\\Package1', $result[0]->getId());
        $this->assertSame(
            [$this->fixturePath('root1/Package1')],
            $result[0]->getPaths()
        );

        $this->assertInstanceOf(NamespaceInterface::class, $result[1]);
        $this->assertSame('Test\\Package3', $result[1]->getId());
        $this->assertSame(
            [$this->fixturePath('root1/Package3')],
            $result[1]->getPaths()
        );
    }

    /**
     * Given a namespace across multiple paths, iterating it's children should produce the correct results.
     */
    public function testIterateMultiPathNamespace()
    {
        $namespace = new Psr4Namespace('Test', $this->fixturePath('root1'), $this->fixturePath('root2'));

        /** @var NamespaceInterface[] $result */
        $result = iterator_to_array($namespace->iterateNamespaces());
        $this->assertCount(3, $result);

        $this->assertInstanceOf(NamespaceInterface::class, $result[0]);
        $this->assertSame('Test\\Package1', $result[0]->getId());
        $this->assertSame(
            [$this->fixturePath('root1/Package1')],
            $result[0]->getPaths()
        );

        $this->assertInstanceOf(NamespaceInterface::class, $result[1]);
        $this->assertSame('Test\\Package2', $result[1]->getId());
        $this->assertSame(
            [$this->fixturePath('root2/Package2')],
            $result[1]->getPaths()
        );

        $this->assertInstanceOf(NamespaceInterface::class, $result[2]);
        $this->assertSame('Test\\Package3', $result[2]->getId());
        $this->assertSame(
            [
                $this->fixturePath('root1/Package3'),
                $this->fixturePath('root2/Package3'),
            ],
            $result[2]->getPaths()
        );
    }


}
