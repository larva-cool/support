<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\Number;

class NumberTest extends TestCase
{
    public function testFloat()
    {
        $a = Number::float(5.325);
        $this->assertIsFloat($a);
        $this->assertTrue($a == 5.33);

        $b = Number::float(5.323);
        $this->assertIsFloat($b);
        $this->assertTrue($b == 5.32);
    }

    public function testCNY()
    {
        $a = Number::cny(5.325);
        $this->assertIsString($a);
        $this->assertTrue($a == '¥5.32');

        $b = Number::cny(5.323);
        $this->assertIsString($a);
        $this->assertTrue($b == '¥5.32');
    }

    public function testFloatWithLimit()
    {
        $this->assertSame(1.24, Number::float(1.236, 2));
        $this->assertSame(1.2, Number::float(1.2, 2));
        $this->assertSame(1.0, Number::float(1, 2));
    }

    public function testPrice()
    {
        // 保留 2 位小数，且不允许负数
        $this->assertSame(9.80, Number::price(9.8));
        $this->assertSame(9.80, Number::price(9.8, 2));
        $this->assertSame(0.00, Number::price(-1.5));
        $this->assertSame(0.0, Number::price(-1.5, 0));
    }

    public function testPriceFormat()
    {
        $this->assertSame('9.80', Number::priceFormat(9.8));
        $this->assertSame('0.00', Number::priceFormat(-1.5));
    }
}
