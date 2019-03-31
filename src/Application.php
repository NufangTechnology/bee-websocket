<?php
namespace Bee\Websocket;

use Bee\Websocket\Middleware\Route;
use Bee\Websocket\Task\RequestBroadcast;
use Bee\Websocket\Task\SendClientConnect;

/**
 * 应用处理实例
 *
 * @package Bee\Websocket
 */
class Application
{
    /**
     * @var array
     */
    protected $meddlers = [];

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Swoole\WebSocket\Server
     */
    protected $server;

    /**
     * Application constructor.
     * @param \Swoole\WebSocket\Server $server
     */
    public function __construct($server)
    {
        $this->server = $server;

        // 注册路由中间件
        $this->use(new Route);
    }

    /**
     * 注册中间件
     *
     * @param Middleware $middleware
     * @return Application
     */
    public function use(Middleware $middleware)
    {
        $this->meddlers[] = $middleware;

        return $this;
    }

    /**
     * 批量注册中间件
     *
     * @param array $config
     * @return Application
     */
    public function map(array $config)
    {
        foreach ($config as $item) {
            $this->use(new $item);
        }

        return $this;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @param Context $context
     * @return Application
     */
    public function setContext(Context $context): Application
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return \Swoole\WebSocket\Server
     */
    public function getServer(): \Swoole\WebSocket\Server
    {
        return $this->server;
    }

    /**
     * 执行应用
     */
    public function handle()
    {
        $result = null;

        // 执行中间件调用
        foreach ($this->meddlers as $middleware) {
            // 执行中间件业务
            $result = call_user_func($middleware, $this, $result);

            // 中间件返回 false ，请求停止向下传递
            if ($result === false) {
                break;
            }
        }

        // 投递请求异步发送消息
        $this->server->task(
            [
                'class' => RequestBroadcast::class,
                'data'  => $this->context->getMessages()
            ]
        );
    }

    /**
     * 注册客户端连接
     *
     * @param $data
     */
    public function registerClientConnect($data)
    {
        $this->server->task(
            [
                'class' => SendClientConnect::class,
                'data'  => $data,
            ]
        );
    }
}
