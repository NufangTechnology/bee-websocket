<?php
require dirname(__DIR__) . '/vendor/autoload.php';

if ($argv[1] == 'master') {
    $server = new \Bee\Websocket\Master(require __DIR__ . '/config.php');
} else {
    $server = new \Bee\Websocket\Slave(require __DIR__ . '/config.php');
}

$server->start();