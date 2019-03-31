<?php
class Master extends \Bee\Websocket\Master
{
    public function onTask($server, \Swoole\Server\Task $task)
    {
    }

    public function onOpen($server, $request)
    {
    }

    public function onMessage($server, \Swoole\WebSocket\Frame $frame)
    {
        print_r($frame);
        $server->push($frame->fd, $frame->data);
    }
}
