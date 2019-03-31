<?php
namespace Bee\Websocket;

use Bee\Websocket\Task\PushMessage;
use Swoole\Server\Task;
use Swoole\WebSocket\Frame;
use Bee\Websocket\Slave\Bridge;

/**
 * 从节点服务
 *
 * @package Bee\Websocket
 * @property \Swoole\WebSocket\Server $swoole
 */
abstract class Slave extends Server
{
    /**
     * @var Bridge
     */
    protected $bridge;

    /**
     * Slave constructor.
     *
     * @param array $runtime
     * @param array $routeRules
     * @throws Exception
     */
    public function __construct(array $runtime, array $routeRules)
    {
        if (!isset($runtime['slave'])) {
            throw new Exception('slave配置不能为空');
        }

        // 需要主节点 host, port 配置参数
        // 从节点需要连接至主节点，必须拥有主节点配置
        if (!isset($runtime['master'])) {
            throw new Exception('master配置不能为空');
        }

        // 传入服务启用配置进行示例化
        parent::__construct($runtime['slave']);

        // 初始化主节点连接器
        $this->bridge = new Bridge($runtime['master'], [$this, 'masterNotify']);

        // 路由配置解析
        // 已经静态方式供组件全局访问
        RouteDispatch::init($routeRules);
    }

    /**
     * 客户端消息接收时回调方法
     *  $frame->data = [
     *      c: 动作码（0,0/主码,子码）
     *      d: 数据体
     *  ]
     *
     * @param \Swoole\WebSocket\Server $server
     * @param Frame $frame
     */
    public function onMessage($server, Frame $frame)
    {
        $data = json_decode($frame->data, true);

        // 初始化上下文，注入客户端数据
        // 执行业务处理
        (new Application($server))
            ->setContext(new Context($frame->fd, $data))
            ->handle()
        ;
    }

    /**
     * 异步任务处理
     *
     * @param \Swoole\WebSocket\Server $server
     * @param Task $task
     */
    public function onTask($server, Task $task)
    {
        //任务的数据
        $params = $task->data;
        // 获取参数
        $class  = $params['class'];
        $method = 'handle';
        $data   = $params['data'];

        // 调起应任务
        (new $class)->{$method}($server, $data);
    }

    /**
     * 接收主节点消息
     *
     * @param $data
     */
    public function masterNotify($data)
    {
        $this->swoole->task(
            [
                'class' => PushMessage::class,
                'data' => $data
            ]
        );
    }
}