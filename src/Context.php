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
     * @var string
     */
    protected $code;

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
        $this->code = $data['c'] ?? '';
        $this->data = $data['d'] ?? [];
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
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
        return $this->data[$key] ?? null;
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
