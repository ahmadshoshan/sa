<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('installments:notify-due')->dailyAt('09:00');