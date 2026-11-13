<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Domain;

use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainEvent;
use PHPUnit\Framework\TestCase;

final class AggregateRootTest extends TestCase
{
    public function testPullReturnsRecordedEventsInOrderThenEmpties(): void
    {
        $first = $this->event();
        $second = $this->event();
        $aggregate = new class extends AggregateRoot {
            public function record(DomainEvent $event): void
            {
                $this->recordThat($event);
            }
        };

        $aggregate->record($first);
        $aggregate->record($second);

        self::assertSame([$first, $second], $aggregate->pullDomainEvents());
        self::assertSame([], $aggregate->pullDomainEvents());
    }

    private function event(): DomainEvent
    {
        return new class implements DomainEvent {
            public function occurredOn(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
        };
    }
}
