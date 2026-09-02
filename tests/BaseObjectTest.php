<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\BaseObject;
use Larva\Support\Exception\InvalidCallException;
use Larva\Support\Exception\UnknownMethodException;
use Larva\Support\Exception\UnknownPropertyException;

/**
 * 简单对象用于测试 BaseObject 的 getter/setter/init
 */
class FakeObject extends BaseObject
{
    public string $name = '';

    public int $age = 0;

    public bool $inited = false;

    public function init(): void
    {
        $this->inited = true;
    }

    public function setName(string $name): void
    {
        $this->name = strtoupper($name);
    }

    public function getName(): string
    {
        return strtolower($this->name);
    }

    public function getReadOnlyAge(): int
    {
        return $this->age;
    }

    public function setReadOnlyAge(int $age): void
    {
        $this->age = $age;
    }

    public function getFullyReadOnly(): int
    {
        return 42;
    }
}

class BaseObjectTest extends TestCase
{
    public function testInit()
    {
        $obj = new FakeObject();
        $this->assertTrue($obj->inited);
    }

    public function testMapConstructor()
    {
        $obj = new FakeObject(['age' => 18]);
        $this->assertSame(18, $obj->age);
    }

    public function testGetterViaMagic()
    {
        $obj = new FakeObject();
        $obj->setName('Hello');
        // 由于 name 是已声明 public 属性，__get 不会触发，直接读 raw value
        $this->assertSame('HELLO', $obj->name);
        // 直接调用 getter 走转换逻辑
        $this->assertSame('hello', $obj->getName());
    }

    public function testSetterViaMagic()
    {
        $obj = new FakeObject();
        $obj->name = 'world';
        // 由于 name 是已声明 public 属性，__set 不会触发，直接写入
        $this->assertSame('world', $obj->name);
        // 通过 setter 方法走转换逻辑
        $obj->setName('foo');
        $this->assertSame('FOO', $obj->name);
    }

    public function testIssetGetter()
    {
        $obj = new FakeObject();
        $obj->age = 5;
        $this->assertTrue(isset($obj->age));
    }

    public function testIssetReturnsFalseForNonExistent()
    {
        $obj = new FakeObject();
        $this->assertFalse(isset($obj->nonexistent));
    }

    public function testUnsetSetterCallsSetter()
    {
        $obj = new FakeObject(['age' => 10]);
        // __unset 会调用 setter，传 null，这里 setReadOnlyAge 不接受 null
        $this->expectException(\TypeError::class);
        unset($obj->readOnlyAge);
    }

    public function testCanGetProperty()
    {
        $obj = new FakeObject();
        $this->assertTrue($obj->canGetProperty('name'));
        $this->assertTrue($obj->canGetProperty('age'));
        $this->assertFalse($obj->canGetProperty('nonExistent'));
    }

    public function testCanSetProperty()
    {
        $obj = new FakeObject();
        $this->assertTrue($obj->canSetProperty('name'));
        $this->assertTrue($obj->canSetProperty('age'));
    }

    public function testHasProperty()
    {
        $obj = new FakeObject();
        $this->assertTrue($obj->hasProperty('name'));
        $this->assertFalse($obj->hasProperty('nonExistent'));
    }

    public function testHasMethod()
    {
        $obj = new FakeObject();
        $this->assertTrue($obj->hasMethod('init'));
        $this->assertFalse($obj->hasMethod('unknownMethod'));
    }

    public function testGettingReadOnlyPropertyThrows()
    {
        $obj = new FakeObject();
        $this->expectException(InvalidCallException::class);
        /** @noinspection PhpUndefinedFieldInspection */
        $obj->fullyReadOnly = 99; // 只有 getter 没有 setter
    }

    public function testSettingReadOnlyProperty()
    {
        $obj = new FakeObject();
        $obj->readOnlyAge = 99;
        $this->assertSame(99, $obj->getReadOnlyAge());
    }

    public function testGettingUnknownPropertyThrows()
    {
        $obj = new FakeObject();
        $this->expectException(UnknownPropertyException::class);
        /** @noinspection PhpUndefinedFieldInspection */
        $obj->nonExistent;
    }

    public function testSettingUnknownPropertyThrows()
    {
        $obj = new FakeObject();
        $this->expectException(UnknownPropertyException::class);
        $obj->nonExistent = 'value';
    }

    public function testCallUnknownMethodThrows()
    {
        $obj = new FakeObject();
        $this->expectException(UnknownMethodException::class);
        $obj->nonExistentMethod();
    }
}
