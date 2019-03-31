<?php


$masterS = new \Swoole\Server('0.0.0.0', 4013);
$masterS->set(
    [
        'pid_file'              => __DIR__ . '/tmp/ws-master.pid',
        'log_file'              => __DIR__ . '/tmp/ws_master.log',
        'worker_num'            => 1,  // 只使用1个进程，防止多进程之间数据同步问题
        'task_worker_num'       => 1,
        'daemonize'             => false,
        'open_cpu_affinity'     => true,
        'task_enable_coroutine' => true,
    ]
);
/** $server  */
$masterS->on('receive', function ($server, $fd, $reactor_id, $data) {
    /** @var \Swoole\Server $server */
    $server->send($fd, 'master-worker: '. $data);

    $server->task(
        [
            'fd' => $fd,
            'data' => 'master-task: ' . $data
        ]
    );
});

$masterS->on('task', function ($server, $task) {
    /** @var \Swoole\Server $server */
    $server->send($task->data['fd'], $task->data['data']);
});

$masterS->start();
