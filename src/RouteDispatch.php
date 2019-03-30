<?php
namespace Bee\Websocket;

use Bee\Router\Router;

/**
 * 路由分发
 *
 * @package Bee\Websocket
 */
class RouteDispatch
{
    /**
     * @var Router
     */
    static protected $router;

    /**
     * @param array $rules
     */
    static public function init(array $rules)
    {
        self::$router = new Router();
        self::$router->map($rules);
    }

    /**
     * 执行路由匹配
     *  - 所有 websocket 路由强制要求使用 GET 进行绑定
     *
     * @param $path
     * @return \Bee\Router\Handler|bool
     */
    static public function match($path)
    {
        // 路径修复
        $path = '/' . trim($path, '/');

        return self::$router->match('GET', $path);
    }
}