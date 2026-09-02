<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\HttpClient;

class SSLCertificateTest extends TestCase
{
    /**
     * 检测域名
     */
    public function testBase()
    {
        $cert = HttpClient::getSSLCert('www.alipay.com');
        $this->assertEquals('*.alipay.com', $cert->getDomain());
        $this->assertTrue($cert->isValid('www.alipay.com'));
        $this->assertTrue($cert->isValid('alipay.com'));
    }
}
