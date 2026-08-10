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

use function array_any;
use function str_ends_with;
use function str_starts_with;
use FilterIterator;
use SplFileInfo;

/**
 * @template-extends FilterIterator<int, SplFileInfo, \Iterator>
 *
 * @internal This class is not covered by the backward compatibility promise for phpunit/php-file-iterator
 */
final class Iterator extends FilterIterator
{
    /**
     * @var list<string>
     */
    private array $suffixes;

    /**
     * @var list<string>
     */
    private array $prefixes;

    /**
     * @param \Iterator<int, SplFileInfo> $iterator
     * @param list<string>                $suffixes
     * @param list<string>                $prefixes
     */
    public function __construct(\Iterator $iterator, array $suffixes = [], array $prefixes = [])
    {
        $this->prefixes = $prefixes;
        $this->suffixes = $suffixes;

        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        $filename = $this->getInnerIterator()->current()->getFilename();

        if ($this->prefixes !== [] &&
            !array_any($this->prefixes, static fn (string $prefix) => str_starts_with($filename, $prefix))) {
            return false;
        }

        if ($this->suffixes !== [] &&
            !array_any($this->suffixes, static fn (string $suffix) => str_ends_with($filename, $suffix))) {
            return false;
        }

        return true;
    }
}
