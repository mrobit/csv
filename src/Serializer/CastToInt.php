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

namespace League\Csv\Serializer;

use ReflectionParameter;
use ReflectionProperty;

use function filter_var;

/**
 * @implements TypeCasting<int|null>
 */
final class CastToInt implements TypeCasting
{
    private readonly bool $isNullable;

    public function __construct(
        ReflectionProperty|ReflectionParameter $reflectionProperty,
        private readonly ?int $default = null,
    ) {
        $this->isNullable = $this->init($reflectionProperty);
    }

    /**
     * @throws TypeCastingFailed
     */
    public function toVariable(?string $value): ?int
    {
        if (null === $value) {
            return match ($this->isNullable) {
                true => $this->default,
                false => throw new TypeCastingFailed('The `null` value can not be cast to an integer; the property type is not nullable.'),
            };
        }

        $int = filter_var($value, Type::Int->filterFlag());

        return match ($int) {
            false => throw new TypeCastingFailed('The `'.$value.'` value can not be cast to an integer.'),
            default => $int,
        };
    }

    private function init(ReflectionProperty|ReflectionParameter $reflectionProperty): bool
    {
        $type = null;
        $isNullable = false;
        foreach (Type::list($reflectionProperty) as $found) {
            if (!$isNullable && $found[1]->allowsNull()) {
                $isNullable = true;
            }

            if (null === $type && $found[0]->isOneOf(Type::Mixed, Type::Int, Type::Float)) {
                $type = $found;
            }
        }

        if (null === $type) {
            throw new MappingFailed('`'.$reflectionProperty->getName().'` type is not supported; `int`, `float` or `null` type is required.');
        }

        return $isNullable;
    }
}
