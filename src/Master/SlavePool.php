<?php
namespace Bee\Websocket\Master;

use Swoole\Table;

/**
 * 子节点连接映射池
 *  - 每个子节点保存最后与通信 fd 用于主节点向子节点发送消息
 *  - 数据结构：
 *      alias => [
 *          fd => 0 // 子节点连接 fd
 *      ]
 *
 * @package Bee\Websocket\Master
 */
class SlavePool
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * ClientPool constructor.
     */
    public function __construct()
    {
        $this->table = new Table(1024 * 500);
        $this->table->column('fd', Table::TYPE_INT);
        $this->table->create();
    }

    /**
     * 注册客户端段连接
     *
     * @param string $key
     * @param int $fd
     */
    public function set($key, $fd)
    {
        $this->table->set($key, ['fd' => $fd]);
    }

    /**
     * 获取指定数据
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->table->get($key);
    }

    /**
     * 删除指定数据
     *
     * @param string $key
     * @return mixed
     */
    public function del(string $key)
    {
        return $this->table->del($key);
    }
}
