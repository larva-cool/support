<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\Url;

class UrlTest extends TestCase
{
    public function testBase()
    {
        $url = new Url("http://username:password@host:8080/directory/file?query#ref");
        $this->assertEquals('http', $url->getScheme());
        $this->assertEquals('host', $url->getHostName());
        $this->assertEquals(8080, $url->getPort());
        $this->assertEquals('/directory/file', $url->getPath());
        $this->assertEquals('query', $url->getQuery());
        $this->assertEquals('ref', $url->getFragment());
    }

    public function testGetScheme()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('http', $url->getScheme());
        $url = new Url("ssl://www.baidu.com:8080");
        $this->assertEquals('ssl', $url->getScheme());
        $url = new Url("https://www.baidu.com:8080");
        $this->assertEquals('https', $url->getScheme());
        $url = new Url("www.baidu.com:8080");
        $this->assertEquals('https', $url->getScheme());
    }

    public function testGetHostName()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('www.baidu.com', $url->getHostName());

        $url = new Url("www.baidu.com");
        $this->assertEquals('www.baidu.com', $url->getHostName());

        $url = new Url("ssl://www.baidu.com");
        $this->assertEquals('www.baidu.com', $url->getHostName());
    }

    public function testGetPort()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals(80, $url->getPort());
        $url = new Url("https://www.baidu.com");
        $this->assertEquals(443, $url->getPort());
        $url = new Url("ssl://www.baidu.com:8080");
        $this->assertEquals(8080, $url->getPort());
    }

    public function testGetUser()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('', $url->getUser());

        $url = new Url("username:password@www.baidu.com");
        $this->assertEquals('username', $url->getUser());

        $url = new Url("https://username:password@www.baidu.com");
        $this->assertEquals('username', $url->getUser());
    }

    public function testGetPassWord()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('', $url->getPassWord());

        $url = new Url("username:password@www.baidu.com");
        $this->assertEquals('password', $url->getPassWord());

        $url = new Url("https://username:password@www.baidu.com");
        $this->assertEquals('password', $url->getPassWord());
    }

    public function testGetPath()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('', $url->getPath());

        $url = new Url("http://www.baidu.com/query#asd");
        $this->assertEquals('/query', $url->getPath());
    }

    public function testGetQuery()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('', $url->getQuery());

        $url = new Url("http://www.baidu.com/query?k=aaa");
        $this->assertEquals('k=aaa', $url->getQuery());

        $url = new Url("http://www.baidu.com/query?k=aaa#asd");
        $this->assertEquals('k=aaa', $url->getQuery());
    }

    public function testGetFragment()
    {
        $url = new Url("http://www.baidu.com");
        $this->assertEquals('', $url->getFragment());

        $url = new Url("http://www.baidu.com#asd");
        $this->assertEquals('asd', $url->getFragment());
    }
}
