<?php
namespace Bee\Websocket\Slave;

class Message
{
    /**
     * @var array
     */
    protected $uuid = [];

    /**
     * @var mixed
     */
    protected $data;

    public function __construct(array $uuid, $data)
    {
        $this->uuid = $uuid;
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getUuid(): array
    {
        return $this->uuid;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
