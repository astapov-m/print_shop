<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wildberries:update-products-command')->everyThreeMinutes();

Schedule::command('wildberries:update-orders-command')->everyThreeMinutes();
