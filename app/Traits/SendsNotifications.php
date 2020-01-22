<?php

namespace App\Traits;

/**
 * A trait for commands that send notifications..
 */
trait SendsNotifications
{
    /**
     * Sends a notification.
     *
     * @param string $message
     */
    private function notification($message)
    {
        $this->notify(config('app.name'), $message, resource_path('assets/img/icon.png'));
    }
}
