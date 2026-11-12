<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class KernelBootTest extends KernelTestCase
{
    public function testKernelBoots(): void
    {
        self::assertTrue(self::bootKernel()->getContainer()->has('kernel'));
    }
}
