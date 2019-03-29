<?php
return [
    'master' => [
        'name' => 'master-ws',
        'host' => '0.0.0.0',
        'port' => 4013,
        'option' => [
            'pid_file'              => __DIR__ . '/tmp/ws-master.pid',
            'log_file'              => __DIR__ . '/tmp/ws_master.log',
            'worker_num'            => 4,  // 只使用1个进程，防止多进程之间数据同步问题
            'task_worker_num'       => 4,
            'daemonize'             => false,
            'open_cpu_affinity'     => true,
            'task_enable_coroutine' => true,
        ],
    ],

    'slave' => [
        'name' => 'slave-ws',
        'host' => '0.0.0.0',
        'port' => 4014,
        'option' => [
            'pid_file'              => __DIR__ . '/tmp/ws-slave_1.pid',
            'log_file'              => __DIR__ . '/tmp/ws_slave_1.log',
            'worker_num'            => 4,  // 只使用1个进程，防止多进程之间数据同步问题
            'task_worker_num'       => 4,
            'daemonize'             => false,
            'open_cpu_affinity'     => true,
            'task_enable_coroutine' => true,
        ],
    ]
];
