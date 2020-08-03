<?php

namespace App\Traits;

trait SendsNotifications
{
    private function notification(string $message): void
    {
        $this->notify(config('app.name'), $message, resource_path('assets/img/icon.png'));
    }
}
