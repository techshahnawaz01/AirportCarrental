<?php

use Illuminate\Support\Facades\Schedule;

// Hourly flight board refresh (no-op when the integration is not configured).
Schedule::command('flights:sync')->hourly()->withoutOverlapping();
