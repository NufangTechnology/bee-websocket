<?php

class Slave extends \Bee\Websocket\Slave
{
    /**
     * @param \Swoole\WebSocket\Server $server
     * @param $workerId
     */
    public function onWorkerStart($server, $workerId)
    {
        if ($server->taskworker) {
            go(function () use ($server) {
                var_dump('taskWorker: ' . \co::getCid());

                $this->bridge->connect();

                $this->bridge->receive(function ($data) use ($server) {
                    print_r($data);
                });
            });
        } else {
            var_dump('worker: ' . \co::getCid());
        }
    }

    public function onMessage($server, \Swoole\WebSocket\Frame $frame)
    {
        $server->task('task recv');
    }

    /**
     * 客户端打开连接时回调方法
     *
     * @param \Swoole\Http\Server $server
     * @param \Swoole\Http\Request $request
     * @return mixed
     */
    public function onOpen($server, $request)
    {
    }

    /**
     * 客户端关闭连接时回调方法
     *  - 清除连接，释放相关资源
     *
     * @param \Swoole\Http\Server $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {
    }

    public function onTask($server, \Swoole\Server\Task $task)
    {
        \Swoole\Timer::tick(1000, function () use ($task) {
            $this->bridge->send([$task->data]);
        });

        var_dump('task: ' . \co::getCid());
    }



    /**
     * 主节点消息通知
     *
     * @param $data
     */
    public function notify($data)
    {
        $this->swoole->task($data);
    }
}
