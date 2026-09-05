<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\Socket;

class SocketTest extends TestCase
{
    public function testConstructorAndProperties()
    {
        $socket = new Socket([
            'host' => '127.0.0.1',
            'port' => 80,
            'timeout' => 5,
        ]);
        $this->assertSame('127.0.0.1', $socket->host);
        $this->assertSame(80, $socket->port);
        $this->assertSame(5, $socket->timeout);
        $this->assertFalse($socket->persistent);
    }

    public function testConnectFailureOnUnreachablePort()
    {
        $socket = new Socket([
            // 1.x.x.x 范围内未使用地址 + 极短超时，确保连接失败
            'host' => '127.0.0.1',
            'port' => 65530,
            'timeout' => 1,
        ]);
        $this->assertFalse($socket->connect());
    }

    public function testDisconnectWhenNotConnected()
    {
        $socket = new Socket([
            'host' => '127.0.0.1',
            'port' => 80,
            'timeout' => 1,
        ]);
        // 未连接时直接 disconnect 应返回 true 且无错误
        $this->assertTrue($socket->disconnect());
    }

    public function testErrorCanBeOverridden()
    {
        $socket = new class([
            'host' => '127.0.0.1',
            'port' => 65530,
            'timeout' => 1,
        ]) extends Socket {
            public string $capturedErrStr = '';

            public int $capturedErrNum = 0;

            public function error(string $errStr, int $errNum): void
            {
                $this->capturedErrStr = $errStr;
                $this->capturedErrNum = $errNum;
            }
        };

        $socket->connect();
        // 失败的连接应触发 error() 回调
        $this->assertNotSame('', $socket->capturedErrStr);
        $this->assertNotSame(0, $socket->capturedErrNum);
    }

    public function testWriteWithoutConnectionReturnsFalse()
    {
        $socket = new Socket([
            'host' => '127.0.0.1',
            'port' => 65530,
            'timeout' => 1,
        ]);
        // write() 内部会尝试 connect，连接失败时返回 false
        $this->assertFalse($socket->write('ping'));
    }

    public function testReadWithoutConnectionReturnsFalse()
    {
        $socket = new Socket([
            'host' => '127.0.0.1',
            'port' => 65530,
            'timeout' => 1,
        ]);
        $this->assertFalse($socket->read());
    }

    public function testPersistentFlag()
    {
        $socket = new Socket([
            'host' => '127.0.0.1',
            'port' => 65530,
            'timeout' => 1,
            'persistent' => true,
        ]);
        $this->assertTrue($socket->persistent);
        // 持久连接尝试连接一个无服务端口，应失败
        $this->assertFalse($socket->connect());
    }
}
