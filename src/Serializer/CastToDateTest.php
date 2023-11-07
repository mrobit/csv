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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

final class CastToDateTest extends TestCase
{
    public function testItCanConvertADateWithoutArguments(): void
    {
        $cast = new CastToDate(DateTime::class);
        $date = $cast->toVariable('2023-10-30');

        self::assertInstanceOf(DateTime::class, $date);
        self::assertSame('30-10-2023', $date->format('d-m-Y'));
    }

    public function testItCanConvertADateWithASpecificFormat(): void
    {
        $cast = new CastToDate(DateTimeInterface::class, null, '!Y-m-d', 'Africa/Kinshasa');
        $date = $cast->toVariable('2023-10-30');

        self::assertInstanceOf(DateTimeImmutable::class, $date);
        self::assertSame('30-10-2023 00:00:00', $date->format('d-m-Y H:i:s'));
        self::assertEquals(new DateTimeZone('Africa/Kinshasa'), $date->getTimezone());
    }

    public function testItCanConvertAnObjectImplementingTheDateTimeInterface(): void
    {
        $cast = new CastToDate(MyDate::class);
        $date = $cast->toVariable('2023-10-30');

        self::assertInstanceOf(MyDate::class, $date);
        self::assertSame('30-10-2023', $date->format('d-m-Y'));
    }

    public function testItCShouldThrowIfNoConversionIsPossible(): void
    {
        $this->expectException(TypeCastingFailed::class);

        (new CastToDate(DateTimeInterface::class))->toVariable('foobar');
    }

    public function testItReturnsNullWhenTheVariableIsNullable(): void
    {
        $cast = new CastToDate('?'.DateTime::class);

        self::assertNull($cast->toVariable(null));
    }

    public function testItCanConvertADateWithADefaultValue(): void
    {
        $cast = new CastToDate('?'.DateTimeInterface::class, '2023-01-01', '!Y-m-d', 'Africa/Kinshasa');
        $date = $cast->toVariable(null);

        self::assertInstanceOf(DateTimeImmutable::class, $date);
        self::assertSame('01-01-2023 00:00:00', $date->format('d-m-Y H:i:s'));
        self::assertEquals(new DateTimeZone('Africa/Kinshasa'), $date->getTimezone());
    }
}

class MyDate extends DateTimeImmutable
{
}
