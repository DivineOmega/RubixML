<?php

namespace Rubix\ML\Extractors;

use RuntimeException;
use Generator;

/**
 * Column Picker
 *
 * An extractor that wraps another iterator and selects and rearranges the columns of the data
 * table according to the user-specified keys. The key of a column may either be a string or a
 * column number (integer) depending on the base iterator.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 */
class ColumnPicker implements Extractor
{
    /**
     * The base iterator.
     *
     * @var iterable<array>
     */
    protected $iterator;

    /**
     * The string and/or integer keys of the columns to iterate over.
     *
     * @var (string|int)[]
     */
    protected $keys;

    /**
     * @param iterable<array> $iterator
     * @param (string|int)[] $keys
     */
    public function __construct(iterable $iterator, array $keys)
    {
        $this->iterator = $iterator;
        $this->keys = $keys;
    }

    /**
     * Return an iterator for the records in the data table.
     *
     * @return \Generator<array>
     */
    public function getIterator() : Generator
    {
        foreach ($this->iterator as $i => $record) {
            $row = [];

            foreach ($this->keys as $key) {
                if (!isset($record[$key])) {
                    throw new RuntimeException("Column $key not found"
                        . " at row offset $i.");
                }

                $row[$key] = $record[$key];
            }

            yield $row;
        }
    }
}
