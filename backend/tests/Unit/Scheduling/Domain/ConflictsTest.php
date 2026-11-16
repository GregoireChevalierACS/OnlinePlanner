<?php

declare(strict_types=1);

namespace App\Tests\Unit\Scheduling\Domain;

use App\Scheduling\Domain\Exception\InvalidStatusTransition;
use App\Scheduling\Domain\Exception\SlotUnavailable;
use App\Shared\Domain\Conflict;
use PHPUnit\Framework\TestCase;

final class ConflictsTest extends TestCase
{
    public function testSlotUnavailableExposesItsReasons(): void
    {
        $conflict = SlotUnavailable::because(['déjà réservé']);

        self::assertInstanceOf(Conflict::class, $conflict);
        self::assertSame(['déjà réservé'], $conflict->reasons());
    }

    public function testStatusTransitionIsAConflictWithoutDetailedReasons(): void
    {
        $conflict = InvalidStatusTransition::notFinishedYet();

        self::assertInstanceOf(Conflict::class, $conflict);
        self::assertSame([], $conflict->reasons());
    }
}
