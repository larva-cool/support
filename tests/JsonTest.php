<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use Larva\Support\Json;
use stdClass;

class JsonTest extends TestCase
{
    public function testEncode()
    {
        $this->assertSame('"hello"', Json::encode('hello'));
        $this->assertSame('123', Json::encode(123));
        $this->assertSame('true', Json::encode(true));
        $this->assertSame('[1,2,3]', Json::encode([1, 2, 3]));
        $this->assertSame('["a","b"]', Json::encode(['a', 'b']));
    }

    public function testEncodePreserveUnicode()
    {
        $this->assertSame('"中国"', Json::encode('中国'));
    }

    public function testDecode()
    {
        $this->assertSame(['a' => 1, 'b' => 2], Json::decode('{"a":1,"b":2}'));
        $this->assertSame([1, 2, 3], Json::decode('[1,2,3]'));
        $this->assertNull(Json::decode(''));
        $this->assertNull(Json::decode(null));
    }

    public function testDecodeAsObject()
    {
        $result = Json::decode('{"a":1}', false);
        $this->assertInstanceOf(stdClass::class, $result);
        $this->assertSame(1, $result->a);
    }

    public function testEncodeEmptyArray()
    {
        // 空数组在 encode 时默认被处理为对象，保持与 json_encode 语义一致
        $this->assertSame('[]', Json::encode([]));
    }

    public function testHtmlEncode()
    {
        $html = Json::htmlEncode(['name' => 'Tom & Jerry "Quoted" <tag>']);
        $this->assertStringContainsString('Tom', $html);
        $this->assertStringContainsString('\u0026', $html);  // & 转义
        $this->assertStringContainsString('\u003C', $html);  // < 转义
    }

    public function testDecodeInvalidThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        Json::decode('{invalid}');
    }

    public function testEncodeInvalidThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        // resource 不能被 JSON 编码
        $resource = fopen('php://memory', 'r');
        Json::encode($resource);
    }
}
