<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain\Appointment;

use App\Scheduling\Domain\Appointment\Customer;
use App\Scheduling\Domain\Appointment\Email;
use App\Scheduling\Domain\Exception\InvalidCustomer;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    public function testTrimsName(): void
    {
        $email = new Email('jane@example.com');
        $customer = new Customer('  Jane Doe ', $email);

        self::assertSame('Jane Doe', $customer->name);
        self::assertSame($email, $customer->email);
    }

    public function testRejectsBlankName(): void
    {
        $this->expectException(InvalidCustomer::class);
        $this->expectExceptionMessage('Le nom du client est obligatoire.');

        new Customer('  ', new Email('jane@example.com'));
    }
}
