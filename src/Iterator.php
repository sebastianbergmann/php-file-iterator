<?php
/*
 * This file is part of php-file-iterator.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\FileIterator;

class Iterator extends \FilterIterator
{
    const PREFIX = 0;
    const SUFFIX = 1;

    /**
     * @var array
     */
    private $suffixes = [];

    /**
     * @var array
     */
    private $prefixes = [];

    /**
     * @var array
     */
    private $exclude = [];

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var bool
     */
    private $excludeAll = false;

    /**
     * @param Iterator $iterator
     * @param array    $suffixes
     * @param array    $prefixes
     * @param array    $exclude
     * @param string   $basePath
     */
    public function __construct(Iterator $iterator, array $suffixes = [], array $prefixes = [], array $exclude = [], $basePath = null)
    {
        $exclude = \array_filter(\array_map('realpath', $exclude));

        if ($basePath !== null) {
            $basePath = \realpath($basePath);
        }

        if ($basePath === false) {
            $basePath = null;
        } else {
            foreach ($exclude as &$_exclude) {
                if ($this->isExcludeParentOrSame($_exclude, $basePath)) {
                    $this->excludeAll = true;
                    continue;
                }

                $_exclude = \str_replace($basePath, '', $_exclude);
            }
        }

        $this->prefixes = $prefixes;
        $this->suffixes = $suffixes;
        $this->exclude  = $exclude;
        $this->basePath = $basePath;

        parent::__construct($iterator);
    }

    public function accept()
    {
        $current  = $this->getInnerIterator()->current();
        $filename = $current->getFilename();
        $realPath = $current->getRealPath();

        if ($this->basePath !== null) {
            $realPath = \str_replace($this->basePath, '', $realPath);
        }

        // Filter files in hidden directories.
        if (\preg_match('=/\.[^/]*/=', $realPath)) {
            return false;
        }

        return $this->acceptPath($realPath) &&
               $this->acceptPrefix($filename) &&
               $this->acceptSuffix($filename);
    }

    private function acceptPath(string $path): bool
    {
        if ($this->excludeAll) {
            return false;
        }

        foreach ($this->exclude as $exclude) {
            if (\strpos($path, $exclude) === 0) {
                return false;
            }
        }

        return true;
    }

    private function acceptPrefix(string $filename): bool
    {
        return $this->acceptSubString($filename, $this->prefixes, self::PREFIX);
    }

    private function acceptSuffix(string $filename): bool
    {
        return $this->acceptSubString($filename, $this->suffixes, self::SUFFIX);
    }

    private function acceptSubString(string $filename, array $subStrings, int $type): bool
    {
        if (empty($subStrings)) {
            return true;
        }

        $matched = false;

        foreach ($subStrings as $string) {
            if (($type === self::PREFIX && \strpos($filename, $string) === 0) ||
                ($type === self::SUFFIX &&
                 \substr($filename, -1 * \strlen($string)) === $string)) {
                $matched = true;
                break;
            }
        }

        return $matched;
    }

    private function isExcludeParentOrSame(string $exclude, string $basePath): bool
    {
        if ($exclude === $basePath) {
            return true;
        }

        $excludeWithSeparator = $exclude . DIRECTORY_SEPARATOR;

        return 0 === \strpos($basePath, $excludeWithSeparator);
    }
}
