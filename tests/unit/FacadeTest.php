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

use function realpath;
use function symlink;
use function unlink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExcludeIterator::class)]
#[CoversClass(Facade::class)]
#[CoversClass(Factory::class)]
#[CoversClass(Iterator::class)]
#[Small]
final class FacadeTest extends TestCase
{
    public static function provider(): array
    {
        $fixtureDirectoryRealpath = self::fixtureDirectoryRealpath();

        return [
            'filter prefix: no, filter suffix: no, excludes: none' => [
                [
                    $fixtureDirectoryRealpath . '/a/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/Prefix.php',
                    $fixtureDirectoryRealpath . '/a/c/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/Suffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/Prefix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/Suffix.php',
                    $fixtureDirectoryRealpath . '/b/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/g/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/f/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/f/h/PrefixSuffix.php',
                ],
                __DIR__ . '/../fixture',
                '',
                '',
                [],
            ],

            'filter prefix: no, filter suffix: no, excludes: yes' => [
                [
                    $fixtureDirectoryRealpath . '/a/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/g/PrefixSuffix.php',
                ],
                __DIR__ . '/../fixture',
                '',
                '',
                [
                    $fixtureDirectoryRealpath . '/a/c',
                    $fixtureDirectoryRealpath . '/b/f',
                ],
            ],

            'filter prefix: yes, filter suffix: no, excludes: none' => [
                [
                    $fixtureDirectoryRealpath . '/a/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/Prefix.php',
                    $fixtureDirectoryRealpath . '/a/c/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/Prefix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/g/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/f/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/f/h/PrefixSuffix.php',
                ],
                __DIR__ . '/../fixture',
                '',
                'Prefix',
                [],
            ],

            'filter prefix: no, filter suffix: yes, excludes: none' => [
                [
                    $fixtureDirectoryRealpath . '/a/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/Suffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/Suffix.php',
                    $fixtureDirectoryRealpath . '/b/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/e/g/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/f/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/b/f/h/PrefixSuffix.php',
                ],
                __DIR__ . '/../fixture',
                'Suffix.php',
                '',
                [],
            ],

            'glob, filter prefix: no, filter suffix: no, excludes: none' => [
                [
                    $fixtureDirectoryRealpath . '/a/c/Prefix.php',
                    $fixtureDirectoryRealpath . '/a/c/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/Suffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/Prefix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/PrefixSuffix.php',
                    $fixtureDirectoryRealpath . '/a/c/d/Suffix.php',
                ],
                __DIR__ . '/../fixture/*/c',
                '',
                '',
                [],
            ],
        ];
    }

    protected function setUp(): void
    {
        $fixtureDirectoryRealpath = self::fixtureDirectoryRealpath();

        symlink(
            $fixtureDirectoryRealpath . '/a/DoesNotExist.php',
            $fixtureDirectoryRealpath . '/a/DoesNotExist.php',
        );
    }

    protected function tearDown(): void
    {
        unlink(self::fixtureDirectoryRealpath() . '/a/DoesNotExist.php');
    }

    #[DataProvider('provider')]
    public function testSomething(array $expected, array|string $paths, array|string $suffixes, array|string $prefixes, array $exclude): void
    {
        $this->assertSame(
            $expected,
            (new Facade)->getFilesAsArray($paths, $suffixes, $prefixes, $exclude)
        );
    }

    private static function fixtureDirectoryRealpath(): string|false
    {
        return realpath(__DIR__ . '/../fixture');
    }
}
