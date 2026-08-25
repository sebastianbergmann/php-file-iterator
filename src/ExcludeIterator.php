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
use function array_map;
use function assert;
use function rtrim;
use function str_starts_with;
use RecursiveDirectoryIterator;
use RecursiveFilterIterator;
use SplFileInfo;

/**
 * @extends RecursiveFilterIterator<string, SplFileInfo, RecursiveDirectoryIterator>
 *
 * @internal This class is not covered by the backward compatibility promise for phpunit/php-file-iterator
 */
final class ExcludeIterator extends RecursiveFilterIterator
{
    /**
     * @var list<string>
     */
    private array $exclude;

    /**
     * @param list<string> $exclude
     */
    public function __construct(RecursiveDirectoryIterator $iterator, array $exclude)
    {
        parent::__construct($iterator);

        $this->exclude = array_map(
            static fn (string $path): string => rtrim($path, '/' . DIRECTORY_SEPARATOR),
            $exclude,
        );
    }

    public function accept(): bool
    {
        $current = $this->current();

        assert($current instanceof SplFileInfo);

        $path = $current->getRealPath();

        if ($path === false) {
            return false;
        }

        foreach ($this->exclude as $exclude) {
            if ($path === $exclude || str_starts_with($path, $exclude . DIRECTORY_SEPARATOR)) {
                return false;
            }
        }

        return true;
    }

    public function hasChildren(): bool
    {
        return $this->getInnerIterator()->hasChildren();
    }

    public function getChildren(): self
    {
        return new self(
            $this->getInnerIterator()->getChildren(),
            $this->exclude,
        );
    }

    public function getInnerIterator(): RecursiveDirectoryIterator
    {
        $innerIterator = parent::getInnerIterator();

        assert($innerIterator instanceof RecursiveDirectoryIterator);

        return $innerIterator;
    }
}
