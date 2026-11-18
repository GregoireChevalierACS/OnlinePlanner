<?php

declare(strict_types=1);

namespace App\Tests\Double;

use App\Shared\Application\Notification\Notification;
use App\Shared\Application\Notification\Notifier;

final class RecordingNotifier implements Notifier
{
    /** @var list<Notification> */
    public array $sent = [];

    public function send(Notification $notification): void
    {
        $this->sent[] = $notification;
    }
}
