<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\UnifiedSocialCreditIdentifier;

/**
 * 测试企业信用代码
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UnifiedSocialCreditIdentifierTest extends TestCase
{
    public function testLen()
    {
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('9111000080210'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000802100433B1'));
        $this->assertTrue(UnifiedSocialCreditIdentifier::validate('91110000802100433B'));
    }

    public function testInfo()
    {
        $info = UnifiedSocialCreditIdentifier::getInfo('9141010056103928XQ');
        $this->assertTrue(isset($info['type']));
    }

    public function testProvince()
    {
        $this->assertSame('110000', UnifiedSocialCreditIdentifier::getProvinceCodeByCreditIdentifier('91110000802100433B'));
        $this->assertSame('北京市', UnifiedSocialCreditIdentifier::getProvinceByCreditIdentifier('91110000802100433B'));
    }

    public function testCity()
    {
        $this->assertSame('410100', UnifiedSocialCreditIdentifier::getCityCodeByCreditIdentifier('9141010056103928XQ'));
        $this->assertSame('郑州市', UnifiedSocialCreditIdentifier::getCityByCreditIdentifier('9141010056103928XQ'));
    }

    public function testDistrict()
    {
        $this->assertSame('410100', UnifiedSocialCreditIdentifier::getDistrictCodeByCreditIdentifier('9141010056103928XQ'));
        $this->assertSame('郑州市', UnifiedSocialCreditIdentifier::getDistrictByCreditIdentifier('9141010056103928XQ'));

        $this->assertSame('410105', UnifiedSocialCreditIdentifier::getDistrictCodeByCreditIdentifier('91410105MA9G98K57A'));
        $this->assertSame('金水区', UnifiedSocialCreditIdentifier::getDistrictByCreditIdentifier('91410105MA9G98K57A'));
    }

    public function testInvalidCharset()
    {
        // I / O / Z / S / V 等被 GB 32100 排除
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000I02100433I'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000O02100433O'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000S02100433S'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000V02100433V'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000Z02100433Z'));
    }

    public function testWrongChecksum()
    {
        // 长度合法、字符合法，但校验位被改错
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('91110000802100433X'));
        $this->assertFalse(UnifiedSocialCreditIdentifier::validate('9141010056103928XX'));
    }

    public function testGenerate()
    {
        $code = UnifiedSocialCreditIdentifier::generate('110000');
        $this->assertSame(18, strlen($code));
        $this->assertTrue(UnifiedSocialCreditIdentifier::validate($code));
        $this->assertSame('北京市', UnifiedSocialCreditIdentifier::getProvinceByCreditIdentifier($code));
    }

    public function testGenerateInvalidArea()
    {
        $this->expectException(\InvalidArgumentException::class);
        UnifiedSocialCreditIdentifier::generate('12345');
    }
}
