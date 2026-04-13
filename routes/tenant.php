<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware(['web'])->group(function () {
    Route::get('/', function () {
        return response()->json([
            'app' => config('app.name'),
            'status' => 'ok',
            'message' => 'MasterPOS backend is running.',
        ]);
    });
});
