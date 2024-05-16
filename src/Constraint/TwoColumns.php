<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv\Constraint;

use League\Csv\Extract;
use League\Csv\InvalidArgument;
use League\Csv\StatementError;
use ReflectionException;

use function array_filter;
use function array_map;
use function is_array;
use function is_int;
use function is_string;

/**
 * Enable filtering a record by comparing the values of two of its column.
 *
 * When used with PHP's array_filter with the ARRAY_FILTER_USE_BOTH flag
 * the record offset WILL NOT BE taken into account
 */
final class TwoColumns implements Predicate
{
    /**
     * @throws StatementError
     */
    private function __construct(
        public readonly string|int $first,
        public readonly Comparison $operator,
        public readonly array|string|int $second,
    ) {
        if (is_array($this->second)) {
            $res = array_filter($this->second, fn (mixed $value): bool => !is_string($value) && !is_int($value));
            if ([] !== $res) {
                throw new StatementError('The second column must be a string, an integer or a list of strings and/or integer.');
            }
        }
    }

    /**
     * @throws InvalidArgument
     * @throws StatementError
     */
    public static function filterOn(
        string|int $firstColumn,
        Comparison|string $operator,
        array|string|int $secondColumn
    ): self {
        if (!$operator instanceof Comparison) {
            $operator = Comparison::fromOperator($operator);
        }

        return new self($firstColumn, $operator, $secondColumn);
    }

    /**
     * @throws InvalidArgument
     * @throws StatementError
     * @throws ReflectionException
     */
    public function __invoke(mixed $value, int|string $key): bool
    {
        $val = match (true) {
            is_array($this->second) => array_map(fn (string|int $column) => Extract::value($value, $column), $this->second),
            default => Extract::value($value, $this->second),
        };

        return Column::filterOn($this->first, $this->operator, $val)($value, $key);
    }
}
