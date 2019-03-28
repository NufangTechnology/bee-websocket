<?php
return [
    'name' => 'slav-ws',
    'host' => '0.0.0.0',
    'port' => 4013,
    'option' => [
        'pid_file'              => __DIR__ . '/tmp/ws-slave.pid',
        'log_file'              => __DIR__ . '/tmp/ws_slave.log',
        'worker_num'            => 1,  // 只使用1个进程，防止多进程之间数据同步问题
        'task_worker_num'       => 1,
        'daemonize'             => false,
        'open_cpu_affinity'     => true,
        'task_enable_coroutine' => true,
    ],

    // 开启分布式支持
    // 配置主节点地址，当前节点作为从节点
    'master' => [
        'host' => '127.0.0.1',
        'port' => 4012
    ]
];