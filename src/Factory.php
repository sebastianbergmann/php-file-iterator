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

use const GLOB_ONLYDIR;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function glob;
use function is_dir;
use function is_string;
use function realpath;
use function sort;
use function stripos;
use function substr;
use AppendIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal This class is not covered by the backward compatibility promise for phpunit/php-file-iterator
 */
final class Factory
{
    /**
     * @param list<non-empty-string>|non-empty-string $paths
     * @param list<non-empty-string>|string           $suffixes
     * @param list<non-empty-string>|string           $prefixes
     * @param list<non-empty-string>                  $exclude
     *
     * @phpstan-ignore missingType.generics
     */
    public function getFileIterator(array|string $paths, array|string $suffixes = '', array|string $prefixes = '', array $exclude = []): AppendIterator
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        $paths   = $this->resolveWildcards($paths);
        $exclude = $this->resolveWildcards($exclude);

        if (is_string($prefixes)) {
            if ($prefixes !== '') {
                $prefixes = [$prefixes];
            } else {
                $prefixes = [];
            }
        }

        if (is_string($suffixes)) {
            if ($suffixes !== '') {
                $suffixes = [$suffixes];
            } else {
                $suffixes = [];
            }
        }

        $iterator = new AppendIterator;

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $iterator->append(
                    new Iterator(
                        $path,
                        new RecursiveIteratorIterator(
                            new ExcludeIterator(
                                new RecursiveDirectoryIterator($path, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS),
                                $exclude,
                            ),
                        ),
                        $suffixes,
                        $prefixes,
                    ),
                );
            }
        }

        return $iterator;
    }

    /**
     * @param list<non-empty-string> $paths
     *
     * @return list<non-empty-string>
     */
    private function resolveWildcards(array $paths): array
    {
        $_paths = [[]];

        foreach ($paths as $path) {
            if ($locals = $this->globstar($path)) {
                $_paths[] = array_map('\realpath', $locals);
            } else {
                // @codeCoverageIgnoreStart
                $_paths[] = [realpath($path)];
                // @codeCoverageIgnoreEnd
            }
        }

        return array_values(array_filter(array_merge(...$_paths)));
    }

    /**
     * @see https://gist.github.com/funkjedi/3feee27d873ae2297b8e2370a7082aad
     *
     * @return list<string>
     */
    private function globstar(string $pattern)
    {
        if (stripos($pattern, '**') === false) {
            $files = glob($pattern, GLOB_ONLYDIR);
        } else {
            $position    = stripos($pattern, '**');
            $rootPattern = substr($pattern, 0, $position - 1);
            $restPattern = substr($pattern, $position + 2);

            $patterns = [$rootPattern . $restPattern];
            $rootPattern .= '/*';

            while ($dirs = glob($rootPattern, GLOB_ONLYDIR)) {
                $rootPattern .= '/*';

                foreach ($dirs as $dir) {
                    $patterns[] = $dir . $restPattern;
                }
            }

            $files = [];

            foreach ($patterns as $pat) {
                $files = array_merge($files, $this->globstar($pat));
            }
        }

        if ($files !== false) {
            $files = array_unique($files);
            sort($files);

            return $files;
        }

        // @codeCoverageIgnoreStart
        return [];
        // @codeCoverageIgnoreEnd
    }
}
