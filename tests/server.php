<?php
require dirname(__DIR__) . '/vendor/autoload.php';

define('SLAVE_ID', time());

class Slave extends \Bee\Websocket\Slave
{
    public function onClose($server, $fd)
    {
    }
}

class Demo
{
    /**
     * @var \Bee\Websocket\Application
     */
    protected $app;
    public function setApp($app)
    {
        $this->app = $app;
    }

    public function __call($name, $arguments)
    {
    }

    public function say()
    {

    }
}

if ($argv[1] == 'master') {
    try {
        $server = new \Bee\Websocket\Master(require __DIR__ . '/config.php');
    } catch (\Bee\Websocket\Exception $e) {
        print_r($e->getMessage());
    }
} else {
    try {
        $rule['demo'] = new \Bee\Router\Collection(Demo::class, '/1');
        $rule['demo']->get('/1', 'say');

        $server = new Slave(require __DIR__ . '/config.php', $rule);
    } catch (\Bee\Websocket\Exception $e) {
        print_r($e->getMessage());
    }
}

$server->start();