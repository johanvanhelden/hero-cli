<?php

declare(strict_types=1);

namespace App\Traits;

trait SendsNotifications
{
    private function notification(string $message): void
    {
        $this->notify(config('app.name'), $message, resource_path('assets/img/icon.png'));
    }
}
