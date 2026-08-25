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
use function file_put_contents;
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
final class UnglobbableDirectoryTest extends TestCase
{
    private ?string $directory = null;

    protected function tearDown(): void
    {
        if ($this->directory === null) {
            return;
        }

        unlink($this->directory . '/a\b/File.php');

        rmdir($this->directory . '/a\b');
        rmdir($this->directory);

        $this->directory = null;
    }

    public function testDirectoryWithNameThatGlobCannotMatchIsTraversedWhenPathEndsWithDirectorySeparator(): void
    {
        $directory = $this->directoryWithNameThatGlobCannotMatch();

        $this->assertSame(
            [$directory . '/a\b/File.php'],
            (new Facade)->getFilesAsArray($directory . '/a\b/', '.php'),
        );
    }

    public function testDirectoryWithNameThatGlobCannotMatchIsTraversedWhenPathDoesNotEndWithDirectorySeparator(): void
    {
        $directory = $this->directoryWithNameThatGlobCannotMatch();

        $this->assertSame(
            [$directory . '/a\b/File.php'],
            (new Facade)->getFilesAsArray($directory . '/a\b', '.php'),
        );
    }

    /**
     * A backslash escapes the character that follows it in a glob pattern.
     * A directory whose name contains a backslash can therefore not be matched
     * by glob() when its name is used as a pattern verbatim.
     *
     * @return non-empty-string
     */
    private function directoryWithNameThatGlobCannotMatch(): string
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('This test does not work on Windows');
        }

        $path = tempnam(sys_get_temp_dir(), 'php-file-iterator');

        unlink($path);
        mkdir($path . '/a\b', 0o755, true);

        file_put_contents($path . '/a\b/File.php', '<?php');

        $this->directory = $path;

        $realPath = realpath($path);

        if ($realPath === false) {
            $this->fail('The path of the temporary directory could not be resolved');
        }

        return $realPath;
    }
}
