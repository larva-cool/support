<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

/**
 * Class Socket
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Socket extends BaseObject
{
    /**
     * @var bool 是否是持久连接
     */
    public bool $persistent = false;

    /**
     * @var string IP
     */
    public string $host;

    /**
     * @var int 端口
     */
    public int $port;

    /**
     * @var int 超时时间
     */
    public int $timeout;

    /**
     * @var resource|false Socket 连接句柄
     */
    private $connection = null;

    /**
     * @var bool Socket 连接状态
     */
    private bool $connected = false;

    /**
     * 连接Socket
     * @return bool
     */
    public function connect(): bool
    {
        if ($this->connection != null) {
            $this->disconnect();
        }

        $errNum = 0;
        $errStr = '';
        if ($this->persistent) {
            $this->connection = @pfsockopen($this->host, $this->port, $errNum, $errStr, $this->timeout);
        } else {
            $this->connection = @fsockopen($this->host, $this->port, $errNum, $errStr, $this->timeout);
        }

        if ($errNum !== 0 || $errStr !== '') {
            $this->error($errStr, $errNum);
        }
        $this->connected = is_resource($this->connection);
        return $this->connected;
    }

    /**
     * 错误信息
     * @param string $errStr
     * @param int $errNum
     */
    public function error(string $errStr, int $errNum): void
    {
    }

    /**
     * 向流写入数据
     * @param string $data
     * @return bool|int
     */
    public function write(string $data): bool|int
    {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        return fwrite($this->connection, $data, strlen($data));
    }

    /**
     * 从流读取
     * @param int $length
     * @return bool|string
     */
    public function read(int $length = 1024): bool|string
    {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        if (!feof($this->connection)) {
            return fread($this->connection, $length);
        } else {
            return false;
        }
    }

    /**
     * 断开连接
     * @return bool
     */
    public function disconnect(): bool
    {
        if (!is_resource($this->connection)) {
            $this->connected = false;
            return true;
        }
        $closed = fclose($this->connection);
        $this->connected = !$closed;
        if ($closed) {
            $this->connection = null;
            return true;
        }
        return false;
    }

    public function __destruct()
    {
        if (is_resource($this->connection)) {
            $this->disconnect();
        }
    }
}
