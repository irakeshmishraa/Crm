<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('followups:send-reminders')->everyFiveMinutes();
Schedule::command('campaigns:process')->everyTenMinutes();
Schedule::command('sheets:auto-sync')->everyThirtyMinutes();
Schedule::command('quotations:check-expiry')->daily();
Schedule::command('logs:cleanup')->weekly();
