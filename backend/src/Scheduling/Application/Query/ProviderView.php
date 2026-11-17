<?php

declare(strict_types=1);

namespace App\Scheduling\Application\Query;

use App\Scheduling\Domain\Provider\Provider;

final readonly class ProviderView
{
    public function __construct(
        public string $id,
        public string $name,
    ) {
    }

    public static function fromProvider(Provider $provider): self
    {
        return new self($provider->id()->value, $provider->name());
    }
}
