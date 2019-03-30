<?php
namespace Bee\Websocket;

use Bee\Websocket\Slave\Message;
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
        $this->bridge = new Bridge($runtime['master'], [$this, 'notify']);

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
        $messages = (new Application($server))
            ->setContext(new Context($frame->fd, $data))
            ->handle()
        ;

        // 获取业务处理后的信息集，通过主节点进行广播
        /** @var Message $message */
        foreach ($messages as $message) {
            $this->bridge->broadcast($message->getUuid(), $message->getData());
        }
    }

    /**
     * 异步任务处理
     *
     * @param \Swoole\WebSocket\Server $server
     * @param Task $task
     */
    public function onTask($server, Task $task)
    {
        foreach ($task->data['f'] as $fd) {
            $server->push($fd, $task->data['d']);
        }
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