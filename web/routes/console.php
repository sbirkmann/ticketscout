<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Event Reminders
use Illuminate\Support\Facades\Schedule;
Schedule::command('orders:event-reminders')->hourly();
Schedule::command('ticket:process-abandoned-carts')->hourly();
Schedule::command('payouts:generate')->dailyAt('02:00');
