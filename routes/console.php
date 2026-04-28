<?php

use Illuminate\Support\Facades\Schedule;

// Auto-suspend trials/expired plans + mark overdue invoices — hourly
Schedule::command('billing:auto-suspend')->hourly();

// Generate monthly invoices on the 1st of every month at 02:00
Schedule::command('billing:generate-monthly')->monthlyOn(1, '02:00');

// WhatsApp reminders for invoices that are due soon / overdue — daily at 09:00.
Schedule::command('invoice:remind')->dailyAt('09:00');
