<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\IPHelper;

class IPHelperTest extends TestCase
{
    public function testIsPrivateForIpV4()
    {
        // 私有网段
        $this->assertTrue(IPHelper::isPrivateForIpV4('127.0.0.1'));
        $this->assertTrue(IPHelper::isPrivateForIpV4('10.1.2.3'));
        $this->assertTrue(IPHelper::isPrivateForIpV4('192.168.0.10'));
        $this->assertTrue(IPHelper::isPrivateForIpV4('172.16.0.1'));
        $this->assertTrue(IPHelper::isPrivateForIpV4('169.254.0.1'));

        // 公网地址
        $this->assertFalse(IPHelper::isPrivateForIpV4('8.8.8.8'));
        $this->assertFalse(IPHelper::isPrivateForIpV4('1.1.1.1'));
    }

    public function testFuzzyIpV4()
    {
        $this->assertSame('192.168.*.*', IPHelper::fuzzyIpV4('192.168.1.10'));
        $this->assertSame('8.8.*.*', IPHelper::fuzzyIpV4('8.8.4.4'));
    }

    public function testFuzzyIpv4End()
    {
        $this->assertSame('192.168.1.*', IPHelper::fuzzyIpv4End('192.168.1.10'));
    }

    public function testStartAndEndIpv4()
    {
        $this->assertSame('192.168.1.0', IPHelper::startIpv4('192.168.1.10'));
        $this->assertSame('192.168.1.255', IPHelper::endIpv4('192.168.1.10'));
    }

    public function testStartAndEndIpv4Long()
    {
        $this->assertSame(ip2long('192.168.1.0'), IPHelper::startIpv4Long('192.168.1.10'));
        $this->assertSame(ip2long('192.168.1.255'), IPHelper::endIpv4Long('192.168.1.10'));
    }

    public function testIp2Long()
    {
        $this->assertSame(ip2long('192.168.1.10'), IPHelper::ip2Long('192.168.1.10'));
    }

    public function testSegmentForIpv4()
    {
        $segment = IPHelper::segmentForIpv4('192.168.1.10');
        $this->assertSame([ip2long('192.168.1.0'), ip2long('192.168.1.255')], $segment);
    }

    public function testGetIpVersion()
    {
        $this->assertSame(IPHelper::IPV4, IPHelper::getIpVersion('192.168.1.1'));
        $this->assertSame(IPHelper::IPV6, IPHelper::getIpVersion('2001:db8::1'));
    }

    public function testGetIPv4Range()
    {
        [$start, $end] = IPHelper::getIPv4Range('192.168.0.0/22');
        $this->assertSame('192.168.0.0', $start);
        $this->assertSame('192.168.3.255', $end);

        [$start2, $end2] = IPHelper::getIPv4Range('10.0.0.0/8');
        $this->assertSame('10.0.0.0', $start2);
        $this->assertSame('10.255.255.255', $end2);
    }

    public function testInRangeIPv4()
    {
        // IP 在 CIDR 内（掩码相同）
        $this->assertTrue(IPHelper::inRange('192.168.1.21', '192.168.1.0/24'));
        $this->assertTrue(IPHelper::inRange('192.168.1.21/32', '192.168.1.0/24'));
        // 超出范围
        $this->assertFalse(IPHelper::inRange('192.168.2.1', '192.168.1.0/24'));
        $this->assertFalse(IPHelper::inRange('10.0.0.1', '192.168.0.0/24'));
        // 不同 IP 协议版本
        $this->assertFalse(IPHelper::inRange('192.168.1.1', '2001:db8::/32'));
    }

    public function testExpandIPv6()
    {
        $expanded = IPHelper::expandIPv6('2001:db8::1');
        $this->assertSame('2001:0db8:0000:0000:0000:0000:0000:0001', $expanded);

        $this->assertFalse(IPHelper::expandIPv6('not-a-valid-ip'));
    }

    public function testIp2bin()
    {
        $v4 = IPHelper::ip2bin('192.168.1.1');
        $this->assertSame(32, strlen($v4));

        $v6 = IPHelper::ip2bin('2001:db8::1');
        $this->assertSame(128, strlen($v6));
    }
}
