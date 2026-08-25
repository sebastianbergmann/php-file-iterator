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
use const GLOB_ONLYDIR;
use function array_merge;
use function array_unique;
use function glob;
use function is_dir;
use function is_string;
use function ltrim;
use function realpath;
use function sort;
use function str_ends_with;
use function strpos;
use function substr;
use AppendIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

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
        $paths    = $this->resolveWildcards($this->toList($paths));
        $exclude  = $this->resolveWildcards($exclude);
        $suffixes = $this->toList($suffixes);
        $prefixes = $this->toList($prefixes);

        $iterator = new AppendIterator;

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $directoryIterator = $this->directoryIterator($path);

            if ($directoryIterator === null) {
                continue;
            }

            $iterator->append(
                new Iterator(
                    new RecursiveIteratorIterator(
                        new ExcludeIterator(
                            $directoryIterator,
                            (string) realpath($path),
                            $exclude,
                        ),
                        RecursiveIteratorIterator::LEAVES_ONLY,
                        RecursiveIteratorIterator::CATCH_GET_CHILD,
                    ),
                    $suffixes,
                    $prefixes,
                ),
            );
        }

        return $iterator;
    }

    /**
     * @param list<non-empty-string>|string $value
     *
     * @return list<non-empty-string>
     */
    private function toList(array|string $value): array
    {
        if (!is_string($value)) {
            return $value;
        }

        return $value === '' ? [] : [$value];
    }

    /**
     * A directory that cannot be opened is skipped instead of aborting the
     * traversal it is part of.
     *
     * This happens when the directory cannot be read by the current user, and
     * it happens when the directory is removed by another process after it was
     * read from its parent directory and before it is opened here.
     *
     * RecursiveIteratorIterator::CATCH_GET_CHILD does this for the directories
     * that are descended into; this method does it for the root of a traversal,
     * which is opened here and not by RecursiveIteratorIterator.
     */
    private function directoryIterator(string $path): ?RecursiveDirectoryIterator
    {
        try {
            return new RecursiveDirectoryIterator($path, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS);
        } catch (UnexpectedValueException) {
            return null;
        }
    }

    /**
     * @param list<non-empty-string> $paths
     *
     * @return list<non-empty-string>
     */
    private function resolveWildcards(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            $endsWithDirectorySeparator = str_ends_with($path, '/') || str_ends_with($path, DIRECTORY_SEPARATOR);

            $matches = $this->globstar($path);

            if ($matches === []) {
                $matches = [$path];
            }

            foreach ($matches as $match) {
                $realPath = realpath($match);

                if ($realPath === false) {
                    continue;
                }

                if ($endsWithDirectorySeparator && is_dir($realPath)) {
                    $realPath .= DIRECTORY_SEPARATOR;
                }

                $result[] = $realPath;
            }
        }

        return $result;
    }

    /**
     * @see https://gist.github.com/funkjedi/3feee27d873ae2297b8e2370a7082aad
     *
     * @return list<string>
     */
    private function globstar(string $pattern): array
    {
        $position = strpos($pattern, '**');

        if ($position === false) {
            $files = glob($pattern, GLOB_ONLYDIR);

            if ($files === false) {
                return []; // @codeCoverageIgnore
            }
        } else {
            // Everything before '**', including the directory separator that
            // precedes it; empty when the pattern begins with '**'.
            $rootPattern = substr($pattern, 0, $position);

            // Everything after '**', beginning with a directory separator.
            $restPattern = substr($pattern, $position + 2);

            // '**' also matches zero directories. $rootPattern already ends
            // with a directory separator, so the leading one of $restPattern
            // is dropped here.
            $patterns = [$rootPattern . ltrim($restPattern, '/' . DIRECTORY_SEPARATOR)];

            $rootPattern .= '*';

            while ($directories = glob($rootPattern, GLOB_ONLYDIR)) {
                $rootPattern .= '/*';

                foreach ($directories as $directory) {
                    $patterns[] = $directory . $restPattern;
                }
            }

            $files = [];

            foreach ($patterns as $_pattern) {
                $files[] = $this->globstar($_pattern);
            }

            $files = array_merge(...$files);
        }

        $files = array_unique($files);

        sort($files);

        return $files;
    }
}
