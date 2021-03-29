<?php

return [

    /**
     * The URL of your Matomo install
     */
    'url' => env('MATOMO_URL', ''),

    /**
     * The id of the site that should be tracked
     */
    'idSite' => env('MATOMO_SITE_ID', 1),

    /**
     * The auth token of your user
     */
    'tokenAuth' => env('MATOMO_AUTH_TOKEN', ''),

    /**
     * For queuing the tracking you can use custom queue names. Use 'default' if you want to run the queued items within the standard queue.
     */
    'queue' => env('MATOMO_QUEUE', 'matomotracker'),

    /**
     * Optionally set a custom queue connection. Laravel defaults to "sync".
     */
    'queueConnection' => env('MATOMO_QUEUE_CONNECTION', 'default'),
];
