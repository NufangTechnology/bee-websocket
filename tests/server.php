<?php
require dirname(__DIR__) . '/vendor/autoload.php';

define('SLAVE_ID', time());

class Slave extends \Bee\Websocket\Slave
{
    public function onClose($server, $fd)
    {
    }
}

if ($argv[1] == 'master') {
    try {
        $server = new Master(require __DIR__ . '/config.php');
    } catch (\Bee\Websocket\Exception $e) {
        print_r($e->getMessage());
    }
} else {
    try {
        $server = new Slave(require __DIR__ . '/config.php', []);
    } catch (\Bee\Websocket\Exception $e) {
        print_r($e->getMessage());
    }
}

$server->start();