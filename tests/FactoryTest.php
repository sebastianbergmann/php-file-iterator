<?php

namespace SebastianBergmann\FileIterator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var Factory
     */
    private $factory;

    public function setUp()
    {
        $this->root = __DIR__;
        $this->factory = new Factory;
    }

    public function testFindFilesInTestDirectory()
    {
        $iterator = $this->factory->getFileIterator($this->root, 'Test.php');
        $files = \iterator_to_array($iterator);

        $this->assertGreaterThanOrEqual(1, count($files));
    }

    public function testFindFilesWithExcludedNonExistingSubdirectory()
    {
        $iterator = $this->factory->getFileIterator($this->root, 'Test.php', '', [$this->root . '/nonExistingDir']);
        $files = \iterator_to_array($iterator);

        $this->assertGreaterThanOrEqual(1, count($files));
    }
}