<?php
namespace Bee\Websocket\Master;

use Swoole\Table;

/**
 * 客户端连接池
 *
 * @package Bee\Websocket\Master
 */
class Connection
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * Connection constructor.
     */
    public function __construct()
    {
        $this->table = new Table(1024 * 1000);
        $this->table->column('fd', Table::TYPE_INT);
        $this->table->column('node_fd', Table::TYPE_INT);
        $this->table->create();
    }

    /**
     * 注册客户端段连接
     *
     * @param array $targets
     * @param $nodeFd
     */
    public function register(array $targets, $nodeFd)
    {
        foreach ($targets as $uuid => $item) {
            $this->table->set($uuid, ['fd' => $item, 'node_fd' => $nodeFd]);
        }
    }

    /**
     * 获取指定数据
     *
     * @param string $uuid
     * @return mixed
     */
    public function get(string $uuid)
    {
        return $this->table->get($uuid);
    }

    /**
     * 删除指定数据
     *
     * @param string $uuid
     * @return mixed
     */
    public function del(string $uuid)
    {
        return $this->table->del($uuid);
    }
}
