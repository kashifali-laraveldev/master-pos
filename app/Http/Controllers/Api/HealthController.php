<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function __invoke()
    {
        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $dbOk = false;
        }

        $storageOk = Storage::disk(config('filesystems.default'))->exists('');

        $queueConnection = config('queue.default');
        $queueOk = true;
        if ($queueConnection === 'database') {
            try {
                DB::table('jobs')->limit(1)->get();
            } catch (\Throwable) {
                $queueOk = false;
            }
        }

        return response()->json([
            'status' => $dbOk ? 'ok' : 'degraded',
            'database' => $dbOk ? 'connected' : 'disconnected',
            'storage' => $storageOk ? 'available' : 'unavailable',
            'queue' => [
                'connection' => $queueConnection,
                'status' => $queueOk ? 'ready' : 'not_ready',
            ],
            'app_env' => config('app.env'),
            'timestamp' => now()->toISOString(),
        ], ($dbOk && $queueOk) ? 200 : 503);
    }
}
