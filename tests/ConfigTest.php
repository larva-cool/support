<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\Config;

class ConfigTest extends TestCase
{
    public function testInheritsCollection()
    {
        $this->assertInstanceOf(\Larva\Support\Collection::class, new Config());
    }

    public function testBasic()
    {
        $config = new Config([
            'app' => [
                'name' => 'Larva',
                'debug' => true,
            ],
            'version' => '1.0.0',
        ]);

        $this->assertSame('Larva', $config->get('app.name'));
        $this->assertTrue($config->get('app.debug'));
        $this->assertSame('1.0.0', $config->get('version'));
        $this->assertNull($config->get('not-exists'));
        $this->assertSame('default', $config->get('not-exists', 'default'));
    }

    public function testSetAndForget()
    {
        $config = new Config();
        $config->set('app.name', 'Test');
        $this->assertSame('Test', $config->get('app.name'));

        $config->forget('app.name');
        $this->assertNull($config->get('app.name'));
    }

    public function testToArray()
    {
        $config = new Config(['a' => 1, 'b' => 2]);
        $this->assertSame(['a' => 1, 'b' => 2], $config->toArray());
    }
}
