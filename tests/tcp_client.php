<?php

/** @var Client $client */
$client = null;

$slaveS = new \Swoole\WebSocket\Server('0.0.0.0', 4012);
$slaveS->set(
    [
        'pid_file'              => __DIR__ . '/tmp/ws-slave.pid',
        'log_file'              => __DIR__ . '/tmp/ws_slave.log',
        'worker_num'            => 1,  // 只使用1个进程，防止多进程之间数据同步问题
        'task_worker_num'       => 1,
        'daemonize'             => false,
        'open_cpu_affinity'     => true,
        'task_enable_coroutine' => true,
    ]
);
$slaveS->on('workerStart', function ($server) {
    global $client;

    /** @var \Swoole\Server $server */
    if ($server->taskworker) {
        go(function () {
            global $client;

            $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
            $client->connect('0.0.0.0', 4013);

            var_dump($client->connected);

            while (true) {
                $data = $client->recv();
                var_dump('task-recv-co: ' . \co::getCid());
                var_dump($data);
            }
        });
    } else {
        $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $client->connect('0.0.0.0', 4013);
        var_dump($client->connected);

        while (true) {
            $data = $client->recv();
            var_dump('worker-recv-co: ' . \co::getCid());
            var_dump('worker: ' . $data);
        }
    }
});

$slaveS->on('message', function ($server, $frame) {
    global $client;
    var_dump('worker-co: ' . \co::getCid());

    $client->send('slave-worker: ' . serialize($frame->data));

    /** @var \Swoole\WebSocket\Server $server */
    $server->task([$frame->data]);

    $server->push($frame->fd, time());
});

$slaveS->on('task', function ($server, $task) {
    global $client;

    var_dump('task-co: ' . \co::getCid());

    $client->send('slave-task: ' . serialize($task->data));
});

$slaveS->start();