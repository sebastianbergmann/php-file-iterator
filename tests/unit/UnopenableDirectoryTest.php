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

use const DIRECTORY_SEPARATOR;
use function chmod;
use function file_put_contents;
use function is_readable;
use function mkdir;
use function realpath;
use function rmdir;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExcludeIterator::class)]
#[CoversClass(Facade::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Iterator::class)]
#[Small]
final class UnopenableDirectoryTest extends TestCase
{
    private ?string $directory = null;

    protected function tearDown(): void
    {
        if ($this->directory === null) {
            return;
        }

        chmod($this->directory . '/unopenable', 0o755);

        unlink($this->directory . '/unopenable/Unreachable.php');
        unlink($this->directory . '/Reachable.php');

        rmdir($this->directory . '/unopenable');
        rmdir($this->directory);

        $this->directory = null;
    }

    public function testDirectoryThatCannotBeOpenedDoesNotAbortTheTraversal(): void
    {
        $directory = $this->directoryWithUnopenableSubdirectory();

        $this->assertSame(
            [$directory . '/Reachable.php'],
            (new Facade)->getFilesAsArray($directory, '.php'),
        );
    }

    public function testDirectoryThatCannotBeOpenedIsSkippedWhenItIsTheRootOfTheTraversal(): void
    {
        $directory = $this->directoryWithUnopenableSubdirectory();

        $this->assertSame(
            [],
            (new Facade)->getFilesAsArray($directory . '/unopenable', '.php'),
        );
    }

    /**
     * @return non-empty-string
     */
    private function directoryWithUnopenableSubdirectory(): string
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('This test does not work on Windows');
        }

        $path = tempnam(sys_get_temp_dir(), 'php-file-iterator');

        unlink($path);
        mkdir($path . '/unopenable', 0o755, true);

        file_put_contents($path . '/Reachable.php', '<?php');
        file_put_contents($path . '/unopenable/Unreachable.php', '<?php');

        chmod($path . '/unopenable', 0o000);

        $this->directory = $path;

        if (is_readable($path . '/unopenable')) {
            $this->markTestSkipped('This test requires a directory that cannot be read by the current user');
        }

        $realPath = realpath($path);

        if ($realPath === false) {
            $this->fail('The path of the temporary directory could not be resolved');
        }

        return $realPath;
    }
}
