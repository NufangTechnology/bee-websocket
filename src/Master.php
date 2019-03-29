<?php
namespace Bee\Websocket;

use Swoole\Http\Request;
use Swoole\Server\Task;
use Swoole\WebSocket\Frame;
use Bee\Websocket\Master\Connection;

/**
 * 主节点服务
 *
 * @package Bee\Websocket
 */
class Master extends Server
{
    /**
     * @var Connection
     */
    protected $connection;

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

        $this->connection = new Connection;
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
        $server->push($request->fd, 'master: ok');
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
     * @param Frame $frame
     */
    public function onMessage($server, Frame $frame)
    {
        $data = unserialize($frame->data);

        switch ($data['a']) {
            case 'register': // 客户端注册
                $this->connection->register($data['c'], $frame->fd);
                break;

            case 'broadcast': // 广播
                // 通过 uuid 获取其所在的从节点 fd 和连接对象 fd
                $server->task($data);
                break;

            default:
                trigger_error('动作"' . $data['a'] . '"不被支持');
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
        $map = [];

        // 将相同自节点用户合并，减少消息通信量
        foreach ($task->data['c'] as $uuid) {
            // 获取连接信息
            $target = $this->connection->get($uuid);

            // 检查子节点连接是否有效
            // 无效则从对象池删除，降低内存使用
            // 当心用户连接进来时会自动重新注册
            if ($server->exist($target['node_fd'])) {
                $map[$target['node_fd']][] = $target['fd'];
            } else {
                $this->connection->del($uuid);
            }
        }

        foreach ($map as $nodeFd => $fds) {
            $server->push($nodeFd, serialize(['f' => $fds, 'd' => $task->data['d']]));
        }
    }
}
