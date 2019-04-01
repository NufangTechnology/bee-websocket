<?php
namespace Bee\Websocket\Slave;

use Swoole\Table;

/**
 * 客户端连接映射池
 *  fd = [
 *      uuid => '', // 客户端唯一标识
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
        $this->table = new Table(1024 * 200);
        $this->table->column('u', Table::TYPE_STRING, 24);
        $this->table->create();
    }

    /**
     * 添加记录
     *
     * @param string $key
     * @param string $uuid
     */
    public function set(string $key, string $uuid)
    {
        $this->table->set($key, ['u' => $uuid]);
    }

    /**
     * 获取记录
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->table->get($key);
    }

    /**
     * 删除记录
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