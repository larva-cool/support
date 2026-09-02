<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\MacAddres;

class MacAddresTest extends TestCase
{
    public function testValidateMacAddress()
    {
        // 冒号分隔
        $this->assertTrue(MacAddres::validateMacAddress('00:1B:44:11:3A:B7'));
        $this->assertTrue(MacAddres::validateMacAddress('00:1b:44:11:3a:b7'));
        // 短横线分隔
        $this->assertTrue(MacAddres::validateMacAddress('00-1B-44-11-3A-B7'));
        // 错误格式
        $this->assertFalse(MacAddres::validateMacAddress('001B44113AB7'));
        $this->assertFalse(MacAddres::validateMacAddress('00:1B:44:11:3A'));  // 长度不够
        $this->assertFalse(MacAddres::validateMacAddress('00:1B:44:11:3A:B7:XX')); // 多余
        $this->assertFalse(MacAddres::validateMacAddress('not-a-mac'));
    }

    public function testGenerateMacAddress()
    {
        $mac = MacAddres::generateMacAddress();
        $this->assertIsString($mac);
        $this->assertMatchesRegularExpression('/^([0-9A-F]{2}:){5}[0-9A-F]{2}$/', $mac);
        $this->assertTrue(MacAddres::validateMacAddress($mac));
    }

    public function testGenerateMultipleAreDifferent()
    {
        $a = MacAddres::generateMacAddress();
        $b = MacAddres::generateMacAddress();
        // 极小概率相同（生成器 16^5 种可能），但正常情况下不同
        $this->assertNotSame($a, $b);
    }
}
