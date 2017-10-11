<?php
declare(strict_types = 1);

namespace Codehulk\Package;

use Exception;

/**
 * Exercise the readme.md class.
 *
 * @package Codehulk\Package
 */
class ReadmeTest extends TestCase
{
    /**
     * Given a package with no readme, we should detect the absence, and return nothing if we request any content.
     */
    public function testNoReadme()
    {
        $readme = new Readme(
            $this->mockPackage($this->fixturePath('readme/None'))
        );
        $this->assertFalse($readme->exists());
        $this->assertSame('', $readme->getContent());
    }

    /**
     * Given a package where multiple readmes are found, attempting to load a readme should fail with an exception.
     *
     * @expectedException Exception
     */
    public function testMultipleReadmes()
    {
        new Readme(
            $this->mockPackage(
                $this->fixturePath('readme/MultipleA'),
                $this->fixturePath('readme/MultipleB')
            )
        );
    }

    /**
     * Given a package that is split across multiple file locations, we should detect a single readme correctly.
     */
    public function testReadmeInSplitPackage()
    {
        $readme = new Readme(
            $this->mockPackage(
                $this->fixturePath('readme/SplitA'),
                $this->fixturePath('readme/SplitB')
            )
        );
        $this->assertTrue($readme->exists());
        $this->assertSame("A split readme.\n", $readme->getContent());
    }

    /**
     * Given a package with a readme, we should successfully be able to read the content.
     */
    public function testWithReadme()
    {
        $readme = new Readme(
            $this->mockPackage($this->fixturePath('readme/Readme'))
        );
        $this->assertTrue($readme->exists());
        $this->assertSame("# Test Readme\nSome content.\n", $readme->getContent());
        $this->assertSame("<h1>Test Readme</h1>\n<p>Some content.</p>", $readme->getContentAsHtml());
    }

    /**
     * Mocks a package.
     *
     * @param string[] ...$paths The paths to mock the package
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|PackageInterface
     */
    private function mockPackage(string ...$paths)
    {
        $package = $this->createMock(PackageInterface::class);
        $package->method('getPaths')
                ->willReturn($paths);
        return $package;
    }
}
