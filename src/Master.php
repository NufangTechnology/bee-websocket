<?php
namespace Bee\Websocket;

use Bee\Websocket\Master\ClientPool;
use Bee\Websocket\Master\SlavePool;
use Bee\Websocket\Task\RegisterClientConnect;
use Bee\Websocket\Task\SendBroadcast;
use Swoole\Server\Task;

/**
 * 主节点服务
 *
 * @package Bee\Websocket
 */
class Master extends Server
{
    /**
     * @var ClientPool
     */
    protected $clientPool;

    /**
     * @var SlavePool
     */
    protected $slavePool;

    /**
     * Bridge constructor.
     *
     * @param array $runtime
     * @throws Exception
     */
    public function __construct(array $runtime)
    {
        if (!isset($runtime['master'])) {
            throw new Exception('master配置不能为空');
        }

        // 传入服务启用配置进行示例化
        parent::__construct($runtime['master']);
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
        $this->slavePool  = new SlavePool;

        // 设置进程名称
        swoole_set_process_name($this->name . ':master');
        // 启动Http服务
        $this->swoole = new \Swoole\Server($this->host, $this->port);
        $this->swoole->set($this->option);
        $this->registerCallback();
        $this->swoole->start();
    }

    /**
     * 客户端消息接收时回调方法
     *  $frame->data = [
     *      a: 动作(register - 连接注册, broadcast - 广播),
     *      c: 连接对象集合(连接对象抽象目标)
     *      d: 数据体
     *  ]
     *
     * @param \Swoole\WebSocket\Server $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive($server, $fd, $reactorId, $data)
    {
        $data = unserialize($data);

        // 替换子节点连接对象映射
        // 保证每次主节点向子节点发送消息时使用最后一次通信的连接
        $this->slavePool->set($data['a'], $fd);

        // 执行子节点请求业务
        switch ($data['c']) {
            case 101: // 客户端连接注册
                $server->task(
                    [
                        'class' => RegisterClientConnect::class,
                        'data'  => $data,
                    ]
                );
                break;

            case 102: // 发起发送广播指令
                $server->task(
                    [
                        'class' => SendBroadcast::class,
                        'data'  => $data,
                    ]
                );
                break;

            default:
                trigger_error("Action '{$data['c']}' not support");
        }
    }

    /**
     * 客户端关闭连接时回调方法
     *  - 清除连接，释放相关资源
     *
     * @param Server $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {
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
        (new $class)->{$method}($server, $data, $this->slavePool, $this->clientPool);
    }
}
