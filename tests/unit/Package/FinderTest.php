<?php
declare(strict_types = 1);

namespace Codehulk\Package;

/**
 * Exercises the package finder.
 *
 * @package Codehulk\Package
 */
class FinderTest extends TestCase
{
    /**
     * Given an empty namespace, we shouldn't find anything.
     */
    public function testEmptyNamespace()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/empty'))],
            ['Test']
        );

        $results = iterator_to_array($finder, false);
        $this->assertEmpty($results);
    }

    /**
     * Given a namespace/root with a single package, the package should be found.
     */
    public function testFindWithSinglePackage()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/one_package'))],
            ['Test']
        );

        /** @var NamespaceInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(1, $results);

        $this->assertSame('Test\\A', $results[0]->getId());
    }

    /**
     * Given a namespace/root with multiple packages, all packages should be found.
     */
    public function testFindWithMultiplePackages()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/multiple_packages'))],
            ['Test']
        );

        /** @var NamespaceInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(3, $results);

        $this->assertSame('Test\\A', $results[0]->getId());
        $this->assertSame('Test\\B', $results[1]->getId());
        $this->assertSame('Test\\C', $results[2]->getId());
    }

    /**
     * Given a namespace/root with a single package and sub-package, both packages should be found, and the sub-package
     *  should correctly reference the parent.
     */
    public function testFindWithSingleSubPackages()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/one_subpackage'))],
            ['Test']
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(2, $results);

        // A is a package with 2 sub-packages.
        // It should be identified correctly as a root package.
        $a = $results[0];
        $this->assertSame('Test\\A', $a->getId());
        $this->assertFalse($a->isSubPackage());
        $this->assertNull($a->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a1 = $results[1];
        $this->assertSame('Test\\A\\1', $a1->getId());
        $this->assertTrue($a1->isSubPackage());
        $this->assertSame($a, $a1->getParent());
    }

    /**
     * Given a namespace/root with a single package and many sub-packages, all packages should be found, and the
     *  sub-packages should correctly reference the parent.
     */
    public function testFindWithMultipleSubPackages()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/multiple_subpackages'))],
            ['Test']
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(3, $results);

        // A is a package with 2 sub-packages.
        // It should be identified correctly as a root package.
        $a = $results[0];
        $this->assertSame('Test\\A', $a->getId());
        $this->assertFalse($a->isSubPackage());
        $this->assertNull($a->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a1 = $results[1];
        $this->assertSame('Test\\A\\1', $a1->getId());
        $this->assertTrue($a1->isSubPackage());
        $this->assertSame($a, $a1->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a2 = $results[2];
        $this->assertSame('Test\\A\\2', $a2->getId());
        $this->assertTrue($a2->isSubPackage());
        $this->assertSame($a, $a2->getParent());
    }

    /**
     * Given a namespace/root with a single package, a sub-package with a readme, and a sub-package without a readme,
     *  all packages should be found, as the presence of a single sub-package with a readme should imply that all
     *  sub-folders of the package are sub-packages.
     */
    public function testFindWithInferredSubPackages()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/inferred_subpackages'))],
            ['Test']
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(3, $results);

        // A is a package with 2 sub-packages.
        // It should be identified correctly as a root package.
        $a = $results[0];
        $this->assertSame('Test\\A', $a->getId());
        $this->assertFalse($a->isSubPackage());
        $this->assertNull($a->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a1 = $results[1];
        $this->assertSame('Test\\A\\1', $a1->getId());
        $this->assertTrue($a1->isSubPackage());
        $this->assertSame($a, $a1->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a2 = $results[2];
        $this->assertSame('Test\\A\\2', $a2->getId());
        $this->assertTrue($a2->isSubPackage());
        $this->assertSame($a, $a2->getParent());
    }

    /**
     * Given a namespace/root that is itself a package, the finder should correctly identify it, and any sub-packages.
     */
    public function testFindWhereNamespaceIsPackage()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test\\A', $this->fixturePath('/finder/is_package'))],
            ['Test']
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(3, $results);

        // A is a package with 2 sub-packages.
        // It should be identified correctly as a root package.
        $a = $results[0];
        $this->assertSame('Test\\A', $a->getId());
        $this->assertFalse($a->isSubPackage());
        $this->assertNull($a->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a1 = $results[1];
        $this->assertSame('Test\\A\\1', $a1->getId());
        $this->assertTrue($a1->isSubPackage());
        $this->assertSame($a, $a1->getParent());

        // A1 has a readme. It should be identified as a sub-package of A.
        $a2 = $results[2];
        $this->assertSame('Test\\A\\2', $a2->getId());
        $this->assertTrue($a2->isSubPackage());
        $this->assertSame($a, $a2->getParent());
    }

    /**
     * Given multiples namespaces and a single root, the finder should find all packages and sub-packages across the
     *  namespaces, with all inference rules working regardless of presence in the same namespace. Package paths should
     *  be a combination of all relevant namespaces.
     */
    public function testFindAcrossSplitNamespace()
    {
        $finder = new Finder(
            [
                new Psr4Namespace('Test', $this->fixturePath('/finder/split_a')),
                new Psr4Namespace('Test', $this->fixturePath('/finder/split_b')),
            ],
            ['Test']
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(5, $results);

        // A is present in the first namespace, with no sub-packages.
        // It should be found, and it's path should reflect the single namespace it was found in.
        $a = $results[0];
        $this->assertSame('Test\\A', $a->getId());
        $this->assertFalse($a->isSubPackage());
        $this->assertNull($a->getParent());
        $this->assertSame(
            [$this->fixturePath('finder/split_a/A')],
            $a->getPaths()
        );

        // B is present in the second namespace, with no sub-packages.
        // It should be found, and it's path should reflect the single namespace it was found in.
        $b = $results[1];
        $this->assertSame('Test\\B', $b->getId());
        $this->assertFalse($b->isSubPackage());
        $this->assertNull($b->getParent());
        $this->assertSame(
            [$this->fixturePath('finder/split_b/B')],
            $b->getPaths()
        );

        // C is present across both namespaces, and has two sub-packages, one in each namespace, with one inferred.
        // All 3 packages should be found, and their paths should reflect the namespaces they were found in.
        $c = $results[2];
        $this->assertSame('Test\\C', $c->getId());
        $this->assertFalse($c->isSubPackage());
        $this->assertNull($c->getParent());
        $this->assertSame(
            [
                $this->fixturePath('finder/split_a/C'),
                $this->fixturePath('finder/split_b/C'),
            ],
            $c->getPaths()
        );

        $c1 = $results[3];
        $this->assertSame('Test\\C\\1', $c1->getId());
        $this->assertTrue($c1->isSubPackage());
        $this->assertSame($c, $c1->getParent());
        $this->assertSame(
            [$this->fixturePath('finder/split_a/C/1')],
            $c1->getPaths()
        );

        $c2 = $results[4];
        $this->assertSame('Test\\C\\2', $c2->getId());
        $this->assertTrue($c2->isSubPackage());
        $this->assertSame($c, $c2->getParent());
        $this->assertSame(
            [$this->fixturePath('finder/split_b/C/2')],
            $c2->getPaths()
        );
    }

    /**
     * Given a single namespace containing multiple roots, all packages should be found.
     */
    public function testFindWithMultipleRoots()
    {
        $finder = new Finder(
            [new Psr4Namespace('Test', $this->fixturePath('/finder/multiple_roots'))],
            [
                'Test\\Root1',
                'Test\\Root2',
            ]
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(2, $results);

        $this->assertSame('Test\\Root1\\A', $results[0]->getId());
        $this->assertSame('Test\\Root2\\B', $results[1]->getId());
    }

    /**
     * Given a namespace with a project, the finder should correctly find all packages.
     */
    public function testFindWithProject() {
        $finder = new Finder(
            [new Psr4Namespace('Company', $this->fixturePath('/finder/one_project'))],
            [
                'Company',
            ]
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(3, $results);

        $this->assertSame('Company\\Project\\A', $results[0]->getId());
        $this->assertSame('Company\\Project\\A\\1', $results[1]->getId());
        $this->assertSame('Company\\Project\\A\\2', $results[2]->getId());
    }

    /**
     * Given a mix of projects and libraries, the finder should correctly find all packages.
     */
    public function testFindWithEverything() {
        $finder = new Finder(
            [new Psr4Namespace('Company', $this->fixturePath('/finder/all'))],
            [
                'Company',
            ]
        );

        /** @var PackageInterface[] $results */
        $results = iterator_to_array($finder, false);
        $this->assertCount(6, $results);

        $this->assertSame('Company\\Library', $results[0]->getId());
        $this->assertSame('Company\\Library\\Sub1', $results[1]->getId());
        $this->assertSame('Company\\Library\\Sub2', $results[2]->getId());
        $this->assertSame('Company\\Project\\Package', $results[3]->getId());
        $this->assertSame('Company\\Project\\Package\\Sub1', $results[4]->getId());
        $this->assertSame('Company\\Project\\Package\\Sub2', $results[5]->getId());
    }
}
