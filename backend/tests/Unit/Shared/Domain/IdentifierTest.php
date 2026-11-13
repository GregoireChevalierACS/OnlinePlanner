<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain;

use App\Shared\Domain\Identifier;
use App\Shared\Domain\InvalidIdentifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final readonly class FooId extends Identifier
{
}

final readonly class BarId extends Identifier
{
}

final class IdentifierTest extends TestCase
{
    public function testExposesValueAndString(): void
    {
        $id = new FooId('abc');

        self::assertSame('abc', $id->value);
        self::assertSame('abc', (string) $id);
    }

    #[DataProvider('blankValues')]
    public function testRejectsBlankValue(string $value): void
    {
        $this->expectException(InvalidIdentifier::class);
        $this->expectExceptionMessage('Un identifiant ne peut pas être vide.');

        new FooId($value);
    }

    /** @return iterable<array{string}> */
    public static function blankValues(): iterable
    {
        yield 'vide' => [''];
        yield 'espaces' => ['   '];
    }

    public function testEqualityRequiresSameTypeAndValue(): void
    {
        self::assertTrue((new FooId('1'))->equals(new FooId('1')));
        self::assertFalse((new FooId('1'))->equals(new FooId('2')));
        self::assertFalse((new FooId('1'))->equals(new BarId('1')));
    }
}
