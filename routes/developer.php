<?php

use App\Http\Middleware\EnsureLocalEnvironment;
use Illuminate\Support\Facades\Route;

Route::middleware([EnsureLocalEnvironment::class, 'auth', 'verified'])->prefix('developer')->group(function () {
    Route::redirect('', 'developer/notification');

    Route::livewire('notification', 'pages::developer.notification')->name('developer.notification');
});
