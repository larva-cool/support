<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2023-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Tests;

use Larva\Support\HtmlHelper;

class HtmlHelperTest extends TestCase
{
    public function testGetOutLink()
    {
        $url = 'https://www.qq.com/';
        $arr = HtmlHelper::getOutLink($url);
        $this->assertIsArray($arr);
    }

    public function testGetHostnames()
    {
        $content = file_get_contents('https://www.qq.com/');
        $arr = HtmlHelper::getHostnames($content);
        $this->assertIsArray($arr);
    }

    public function testGetHtmlOutLink()
    {
        $content = file_get_contents('https://www.qq.com/');
        $arr = HtmlHelper::getHtmlOutLink($content, 'www.qq.com');
        $this->assertIsArray($arr);
    }
}
