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

use function array_unique;
use function assert;
use function is_file;
use function is_string;
use function realpath;
use function sort;
use SplFileInfo;

final class Facade
{
    /**
     * @psalm-return list<string>
     */
    public function getFilesAsArray(array|string $paths, array|string $suffixes = '', array|string $prefixes = '', array $exclude = []): array
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        $iterator = (new Factory)->getFileIterator($paths, $suffixes, $prefixes, $exclude);

        $files = [];

        foreach ($iterator as $file) {
            assert($file instanceof SplFileInfo);

            $file = $file->getRealPath();

            if ($file) {
                $files[] = $file;
            }
        }

        foreach ($paths as $path) {
            if (is_file($path)) {
                $realpath = realpath($path);

                assert($realpath !== false);

                $files[] = $realpath;
            }
        }

        $files = array_unique($files);

        sort($files);

        return $files;
    }
}
