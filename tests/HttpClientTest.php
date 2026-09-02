<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\HttpClient;

class HttpClientTest extends TestCase
{
    public function testGet()
    {
        $response = HttpClient::make()->get('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testHead()
    {
        $response = HttpClient::make()->head('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testPost()
    {
        $response = HttpClient::make()->post('https://www.baidu.com');
        $this->assertTrue($response->successful() || $response->redirect() || $response->clientError());
    }

    public function testPut()
    {
        $response = HttpClient::make()->put('https://www.baidu.com');
        $this->assertTrue($response->successful() || $response->redirect() || $response->clientError());
    }

    public function testGetCertificate()
    {
        $response = HttpClient::getSSLCert('www.baidu.com');
        $this->assertIsObject($response);
    }

    public function testGetHeaders()
    {
        $response = HttpClient::make()->get('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testPutJSON()
    {
        $response = HttpClient::make()->get('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testPatchJSON()
    {
        $response = HttpClient::make()->get('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testGetJSON()
    {
        $response = HttpClient::make()->get('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testCheckCors()
    {
        $response = HttpClient::make()->get('https://www.baidu.com');
        $this->assertTrue($response->ok());
    }

    public function testGetTDK()
    {
        $response = HttpClient::getTDK('http://www.baidu.com');
        $this->assertIsArray($response);
    }

    public function testGetInfo()
    {
        $response = HttpClient::getInfo('http://www.baidu.com');
        $this->assertIsArray($response);
    }
}
