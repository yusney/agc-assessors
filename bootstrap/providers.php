<?php

use App\Providers\AppServiceProvider;
use App\Providers\DomainServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\OfficesServiceProvider;

return [
    AppServiceProvider::class,
    DomainServiceProvider::class,
    OfficesServiceProvider::class,
    AdminPanelProvider::class,
];
