<?php
require dirname(__DIR__) . '/vendor/autoload.php';

class Server extends \Bee\Websocket\Server
{
    /**
     * Worker进程/Task进程启动时回调此方法
     *
     * @param \Swoole\Http\Server $server
     * @param integer $workerId
     */
    public function onWorkerStart($server, $workerId)
    {
        if ($this->isSlave && $workerId == 0) {
            $this->master->connect();

            var_dump($workerId);
        }
    }

    /**
     * 客户端打开连接时回调方法
     *
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request $request
     * @return mixed
     */
    public function onOpen($server, $request)
    {
        if ($this->isSlave && $server->worker_id == 0) {
            echo '------------- register ------------' . "\n";
            echo $request->getData() . "\n\n";
            $this->master->send($request->fd);
        }

        $server->push($request->fd, 'open');
    }

    /**
     * 客户端消息接收时回调方法
     *
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onMessage($server, \Swoole\WebSocket\Frame $frame)
    {
//        if ($this->isSlave) {

//        } else {
            echo '------------- server ------------' . "\n";
            echo $frame->data . "\n\n";
            $server->push($frame->fd, 'server: ' . $frame->data);
//        }
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
}

if ($argv[1] == 'master') {
    $server = new Server(require __DIR__ . '/master_config.php');
} else {
    $server = new Server(require __DIR__ . '/slave_config.php');
}

$server->start();