<?php
namespace Bee\Websocket\Master;

use Swoole\Table;

/**
 * 客户端连接映射池
 *  uuid = [
 *      fd    => 0, // 客户端连接 fd
 *      slave => '' // 从节点别名，由SlaveConnect映射
 *  ];
 *
 * @package Bee\Websocket\Master
 */
class ClientPool
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
        $this->table = new Table(1024 * 5000);
        $this->table->column('fd', Table::TYPE_INT);
        $this->table->column('slave', Table::TYPE_STRING, 24);
        $this->table->create();
    }

    /**
     * 注册客户端段连接
     *
     * @param array $targets
     * @param string $slave
     */
    public function set(array $targets, $slave)
    {
        foreach ($targets as $uuid => $fd) {
            $this->table->set($uuid, ['fd' => $fd, 'slave' => $slave]);
        }

        var_dump($this->table->getMemorySize());
        var_dump($this->table->count());
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

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }
}
