<?php

function log_activity(string $action, array $context = []): void
{
    $user = isset($_SESSION['user_id'])
        ? $_SESSION['username'] . ' (id:' . $_SESSION['user_id'] . ')'
        : 'unauthenticated';

    $parts = ["[$action]", "user:$user"];

    foreach ($context as $key => $value) {
        $parts[] = "$key:$value";
    }

    $line = '[' . date('Y-m-d H:i:s') . '] ' . implode(' ', $parts) . PHP_EOL;
    error_log($line, 3, '/var/log/migmaq.log');
}
