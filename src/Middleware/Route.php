<?php
namespace Bee\Websocket\Middleware;

use Bee\Websocket\Exception;
use Bee\Websocket\Middleware;
use Bee\Websocket\Application;
use Bee\Websocket\Context;
use Bee\Websocket\RouteDispatch;

/**
 * 路由中间件
 *
 * @package Bee\Websocket\Middleware
 */
class Route extends Middleware
{
    /**
     * 中间件业务执行体
     *
     * @param Application $application
     * @param Context $context
     * @param mixed $parameters
     * @return mixed
     * @throws Exception
     * @throws \Bee\Router\Exception
     */
    public function call(Application $application, Context $context, $parameters = null)
    {
        // 将客户端请求码（主码/子码）转换为 URL 路径风格
        $path    = str_replace(',', '/', $context->getCode());

        $handler = RouteDispatch::match($path);
        // 不存在匹配路由，直接切断客户端连接
        if ($handler === false) {
            throw new Exception('Not match', 0, [$path]);
        }

        $handler->callMethod($application);

        return true;
    }
}