<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Appointment\Email;
use App\Scheduling\Domain\Exception\InvalidEmail;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testNormalizes(): void
    {
        $email = new Email('  Jane.Doe@Example.COM ');

        self::assertSame('jane.doe@example.com', $email->value);
        self::assertSame('jane.doe@example.com', (string) $email);
    }

    public function testRejectsInvalidAddress(): void
    {
        $this->expectException(InvalidEmail::class);
        $this->expectExceptionMessage('« pas-un-mail » n\'est pas une adresse e-mail valide.');

        new Email('pas-un-mail');
    }
}
