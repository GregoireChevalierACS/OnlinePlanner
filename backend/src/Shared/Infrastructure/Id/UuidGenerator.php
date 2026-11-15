<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Id;

use App\Shared\Application\IdGenerator;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Uid\Uuid;

/**
 * UUID v7 : triables dans le temps, donc favorables aux index.
 */
#[AsAlias(IdGenerator::class)]
final readonly class UuidGenerator implements IdGenerator
{
    public function next(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
