<?php
namespace Bee\Websocket;

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;

/**
 * Interface ServerInterface
 *
 * @package Bee\Websocket
 */
interface ServerInterface
{
    /**
     * 客户端打开连接时回调方法
     *
     * @param Server $server
     * @param Request $request
     * @return mixed
     */
    public function onOpen($server, $request);

    /**
     * 客户端消息接收时回调方法
     *
     * @param Server $server
     * @param $frame
     */
    public function onMessage($server, Frame $frame);

    /**
     * 客户端关闭连接时回调方法
     *  - 清除连接，释放相关资源
     *
     * @param Server $server
     * @param $fd
     */
    public function onClose($server, $fd);
}
