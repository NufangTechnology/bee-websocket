<?php
namespace Bee\Websocket;

use Bee\Http\Server as HttpServer;
use Bee\Websocket\Slave\Master;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Websocket Server
 *
 * @package Bee\Websocket
 */
abstract class Server extends HttpServer implements ServerInterface
{
    /**
     * 是否为从节点
     *
     * @var bool
     */
    protected $isSlave = false;

    /**
     * @var Master
     */
    protected $master;

    public function __construct(array $runtime)
    {
        parent::__construct($runtime);

        if (isset($runtime['master'])) {
            $this->isSlave = true;

            $config = $runtime['master'];
            $this->master = new Master($config['host'], $config['port']);
        }
    }

    public function setMasterConfig($params)
    {
        $this->isSlave = true;
        $this->master  = $params;
    }

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
}