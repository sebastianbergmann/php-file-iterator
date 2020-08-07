<?php declare(strict_types=1);
/*
 * This file is part of phpunit/php-file-iterator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\FileIterator;

use function count;
use function iterator_to_array;
use PHPUnit\Framework\TestCase;

/**
 * @covers \SebastianBergmann\FileIterator\Factory
 *
 * @uses \SebastianBergmann\FileIterator\Iterator
 */
final class FactoryTest extends TestCase
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var Factory
     */
    private $factory;

    protected function setUp(): void
    {
        $this->root    = __DIR__;
        $this->factory = new Factory;
    }

    public function testFindFilesInTestDirectory(): void
    {
        $iterator = $this->factory->getFileIterator($this->root, 'Test.php');
        $files    = iterator_to_array($iterator, false);

        $this->assertGreaterThanOrEqual(1, count($files));
    }

    public function testFindFilesWithExcludedNonExistingSubdirectory(): void
    {
        $iterator = $this->factory->getFileIterator($this->root, 'Test.php', '', [$this->root . '/nonExistingDir']);
        $files    = iterator_to_array($iterator, false);

        $this->assertGreaterThanOrEqual(1, count($files));
    }
}
