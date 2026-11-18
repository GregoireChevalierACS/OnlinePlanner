<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Scheduling\Application\Notification\Notification;
use App\Scheduling\Application\Notification\Notifier;

final class RecordingNotifier implements Notifier
{
    /** @var list<Notification> */
    public array $sent = [];

    public function send(Notification $notification): void
    {
        $this->sent[] = $notification;
    }
}
