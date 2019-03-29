<?php
namespace Bee\Websocket\Master;

use Swoole\Table;

class Connection
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var Slave
     */
    protected $slave;

    /**
     * Connection constructor.
     */
    public function __construct()
    {
        $this->table = new Table(1024 * 1000);
        $this->table->column('fd', Table::TYPE_INT);
        $this->table->column('node_fd', Table::TYPE_INT);
        $this->table->create();

        $this->slave = new Slave;
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

        var_dump($this->table->getMemorySize());
        var_dump($this->table->count());
    }

    public function get($uuid)
    {
        return $this->table->get($uuid);
    }
}
