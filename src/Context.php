<?php
namespace Bee\Websocket;

/**
 * 上下文
 *
 * @package Bee\Websocket
 */
class Context
{
    /**
     * @var int
     */
    protected $fd;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * Context constructor.
     *
     * @param int $fd
     * @param $data
     */
    public function __construct(int $fd, $data)
    {
        $this->fd   = $fd;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * @param array $uuid
     * @param mixed $data
     */
    public function message(array $uuid, $data): void
    {
        $this->messages[] = ['u' => $uuid, 'd' => $data];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return int
     */
    public function getMessageLength(): int
    {
        return count($this->messages);
    }
}
