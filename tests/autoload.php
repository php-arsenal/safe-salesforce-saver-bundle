<?php

require __DIR__.'/../vendor/autoload.php';

$usePutenv = false;

(new Symfony\Component\Dotenv\Dotenv($usePutenv))->load(__DIR__.'/../.env');
(new Symfony\Component\Dotenv\Dotenv($usePutenv))->load(__DIR__.'/../.env.test');

if (file_exists(__DIR__.'/../.env.test.local')) {
    (new Symfony\Component\Dotenv\Dotenv($usePutenv))->load(__DIR__.'/../.env.test.local');
}

if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    passthru(sprintf(
        'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
        __DIR__,
        $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
    ));
}
