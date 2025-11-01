<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    */

    'domain' => env('HORIZON_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    */

    'waits' => [
        'redis:import' => 60,
        'redis:orders' => 30,
        'redis:notifications' => 15,
        'redis:refunds' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    */

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    */

    'silenced' => [
        // App\Jobs\ExampleJob::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    */

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    */

    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => 128,
            'tries' => 3,
            'timeout' => 60,
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-import' => [
                'connection' => 'redis',
                'queue' => ['import'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 2,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 256,
                'tries' => 3,
                'timeout' => 300,
                'nice' => 0,
            ],
            'supervisor-orders' => [
                'connection' => 'redis',
                'queue' => ['orders'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 5,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 120,
                'nice' => 0,
            ],
            'supervisor-notifications' => [
                'connection' => 'redis',
                'queue' => ['notifications'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 3,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 60,
                'nice' => 0,
            ],
            'supervisor-refunds' => [
                'connection' => 'redis',
                'queue' => ['refunds'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'time',
                'maxProcesses' => 2,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 120,
                'nice' => 0,
            ],
        ],

        'local' => [
            'supervisor-import' => [
                'connection' => 'redis',
                'queue' => ['import'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'simple',
                'maxProcesses' => 1,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 300,
                'nice' => 0,
            ],
            'supervisor-orders' => [
                'connection' => 'redis',
                'queue' => ['orders'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'simple',
                'maxProcesses' => 2,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 120,
                'nice' => 0,
            ],
            'supervisor-notifications' => [
                'connection' => 'redis',
                'queue' => ['notifications'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'simple',
                'maxProcesses' => 1,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 60,
                'nice' => 0,
            ],
            'supervisor-refunds' => [
                'connection' => 'redis',
                'queue' => ['refunds'],
                'balance' => 'auto',
                'autoScalingStrategy' => 'simple',
                'maxProcesses' => 1,
                'maxTime' => 0,
                'maxJobs' => 0,
                'memory' => 128,
                'tries' => 3,
                'timeout' => 120,
                'nice' => 0,
            ],
        ],
    ],
];
