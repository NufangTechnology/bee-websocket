<?php
namespace Bee\Websocket;

use Bee\Http\Server as HttpServer;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

/**
 * Websocket Server
 *
 * @package Bee\Websocket
 */
abstract class Server extends HttpServer implements ServerInterface
{
    /**
     * 服务启动
     */
    public function start()
    {
        if ($this->isRunning()) {
            $this->output->warn("无效操作，服务已经在[{$this->host}:{$this->port}]运行！");
            return;
        }

        // 设置进程名称
        swoole_set_process_name($this->name . ':master');
        // 启动Http服务
        $this->swoole = new \Swoole\WebSocket\Server($this->host, $this->port);
        $this->swoole->set($this->option);
        $this->registerCallback();
        $this->swoole->start();
    }

    /**
     * 内部消化 http 请求
     * 如果相对外提供 http 能力，直接覆盖该方法即可
     *
     * @param Request $request
     * @param Response $response
     */
    public function onRequest(Request $request, Response $response)
    {
        $response->end('ok');
    }

    /**
     * 客户端打开连接时回调方法
     *  - 检查进来的连接是否合法
     *
     * @param \Swoole\WebSocket\Server $server
     * @param Request $request
     */
    public function onOpen($server, $request)
    {
        $server->push($request->fd, 'ok');
    }

    /**
     * 客户端消息接收时回调方法
     *
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onMessage($server, Frame $frame)
    {
        $server->push($frame->fd, 'ok');
    }
}