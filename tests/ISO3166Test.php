<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\ISO3166;

class ISO3166Test extends TestCase
{
    public function testCountry()
    {
        $country = ISO3166::country('CN', 'en');
        $this->assertTrue($country == 'China');

        $country = ISO3166::country('CN', 'zh-cn');
        $this->assertTrue($country == '中国');
    }

    public function testCountryCode()
    {
        $country = ISO3166::countryCode('中国');
        $this->assertTrue($country == 'CN');

        $country = ISO3166::countryCode('台湾');
        $this->assertFalse($country == 'CN');
    }

    public function testPosition()
    {
        $loc = ISO3166::position('CN');
        $this->assertIsArray($loc);
    }

    public function testPhoneCode()
    {
        $code = ISO3166::phoneCode('CN');
        $this->assertTrue($code == '86');
    }

    public function testIsValid()
    {
        $valid = ISO3166::isValid('CN');
        $this->assertTrue($valid);

        $valid = ISO3166::isValid('CNCC');
        $this->assertFalse($valid);
    }
}
