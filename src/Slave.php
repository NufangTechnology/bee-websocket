<?php
namespace Bee\Websocket;

use Bee\Websocket\Slave\ClientPool;
use Bee\Websocket\Task\PushMessage;
use Bee\Websocket\Task\RequestBroadcast;
use Bee\Websocket\Task\SendClientConnect;
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
     * @var ClientPool
     */
    protected $clientPool;

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
     * 启动服务
     */
    public function start()
    {
        if ($this->isRunning()) {
            $this->output->warn("无效操作，服务已经在[{$this->host}:{$this->port}]运行！");
            return;
        }

        $this->clientPool = new ClientPool;

        // 设置进程名称
        swoole_set_process_name($this->name . ':master');
        // 启动Http服务
        $this->swoole = new \Swoole\WebSocket\Server($this->host, $this->port);
        $this->swoole->set($this->option);
        $this->registerCallback();
        $this->swoole->start();
    }

    /**
     * 进程启动
     *
     * @param \Swoole\WebSocket\Server $server
     * @param int $workerId
     */
    public function onWorkerStart($server, $workerId)
    {
        parent::onWorkerStart($server, $workerId); // TODO: Change the autogenerated stub

        // 只在 task 进程中启动与主节点连接
        // 这意味着所有通过主节点广播的消息必须在 tas 进程中进行
        if ($server->taskworker) {
            go(function () {
                $this->bridge->reconnect();
                $this->bridge->receive();
            });
        }
    }

    /**
     * 客户端打开连接时回调方法
     *  - 连接检查/身份鉴权
     *
     * @param \Swoole\WebSocket\Server $server
     * @param \Swoole\Http\Request $request
     * @return mixed|void
     */
    public function onOpen($server, $request)
    {
        // 初始化上下文，构造身份鉴别请求参数
        $context = new Context(
            $request->fd,
            [
                'c' => '1,1', // 1,1 默认为身份鉴定路由地址（从0开始会路由转换异常）
                'd' => [
                    'token' => $request->header['x-token'] ?? ''
                ]
            ]
        );

        // 执行业务处理
        (new Application)
            ->setContext($context)
            ->handle()
        ;

        // 获取设备(用户)唯一标识
        $uuid = $context->get('uuid');
        // 身份识别失败
        // 关闭连接，节省连接，防止恶意占用连接
        if (empty($uuid)) {
            $server->push($request->fd, 'unauthorized');
            $server->close($request->fd);
        }

        // 连接与用户映射
        $this->clientPool->set($request->fd, $uuid);
        // 注册当前连接至至主节点
        $this->registerClientConnect([$uuid => $request->fd]);
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
        // 客户端请求数据格式强制为 json
        $data    = json_decode($frame->data, true);

        // 初始化上下文，注入客户端数据
        $context = new Context($frame->fd, $data);

        // 还原当前连接标识
        $mapping = $this->clientPool->get($frame->fd);
        $context->set('uuid', $mapping['u']);

        // 执行业务处理
        (new Application)
            ->setContext($context)
            ->handle()
        ;

        // 投递请求异步发送消息
        if ($context->getMessageLength()) {
            $server->task(
                [
                    'class' => RequestBroadcast::class,
                    'data'  => $context->getMessages()
                ]
            );
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
        //任务的数据
        $params = $task->data;
        // 获取参数
        $class  = $params['class'];
        $method = 'handle';
        $data   = $params['data'];

        // 调起应任务
        (new $class)->{$method}($server, $this->bridge, $data);
    }

    /**
     * 客户端关闭连接时回调方法
     *
     * @param \Swoole\WebSocket\Server $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {
        // 删除映射
        $this->clientPool->del($fd);
    }

    /**
     * 接收主节点消息
     *
     * @param $data
     */
    public function masterNotify($data)
    {
        (new PushMessage)->handle($this->swoole, $this->bridge, $data);
    }

    /**
     * 注册客户端连接
     *
     * @param array $data
     */
    public function registerClientConnect($data)
    {
        $this->swoole->task(
            [
                'class' => SendClientConnect::class,
                'data'  => $data
            ]
        );
    }
}