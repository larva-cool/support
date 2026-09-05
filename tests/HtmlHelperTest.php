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

    public function testGetOutLinkInvalidUrlReturnsEmpty()
    {
        $this->assertSame([], HtmlHelper::getOutLink('not a url'));
    }

    public function testPurify()
    {
        $html = '<script>alert(1)</script><p>Hello <b>World</b></p>';
        $clean = HtmlHelper::purify($html);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringContainsString('Hello', $clean);
        $this->assertStringContainsString('World', $clean);
    }

    public function testCleanUtf8()
    {
        $this->assertIsString(HtmlHelper::cleanUtf8('Hello 中文 World'));
    }

    public function testEncodeAndDecode()
    {
        $original = '<a href="x">Tom & "Jerry"</a>';
        $encoded = HtmlHelper::encode($original);
        $this->assertStringNotContainsString('<', $encoded);
        $this->assertStringNotContainsString('>', $encoded);
        $this->assertStringNotContainsString('"', $encoded);
        $this->assertStringContainsString('&amp;', $encoded);
        $this->assertSame($original, HtmlHelper::decode($encoded));
    }

    public function testEncodeDoubleEncodeFalse()
    {
        // 已编码的实体不应再被二次编码
        $this->assertSame('&amp;', HtmlHelper::encode('&amp;', false));
        $this->assertSame('&amp;amp;', HtmlHelper::encode('&amp;', true));
    }

    public function testEncodeParams()
    {
        $html = 'Hello {name}, age {age}!';
        $result = HtmlHelper::encodeParams($html, ['name' => '<Tom>', 'age' => 18]);
        $this->assertStringContainsString('&lt;Tom&gt;', $result);
        $this->assertStringContainsString('18', $result);
        // 数字不会被编码
        $this->assertStringNotContainsString('&#039;18&#039;', $result);
    }

    public function testEncodeParamsWithBraces()
    {
        // 模板中使用规范的 {user} 形式
        $html = 'Hi {user}!';
        $result = HtmlHelper::encodeParams($html, ['user' => 'Tom']);
        $this->assertSame('Hi Tom!', $result);
    }

    public function testGetCharSetFromMeta()
    {
        $content = '<html><head><meta charset="UTF-8"></head><body></body></html>';
        $this->assertSame('UTF-8', HtmlHelper::getCharSet($content));

        $content2 = '<html><head><meta charset="gbk"></head></html>';
        $this->assertSame('GBK', HtmlHelper::getCharSet($content2));
    }

    public function testGetCharSetFallbackToMbDetect()
    {
        $content = '中文字符串无meta'; // 不含 meta
        $charset = HtmlHelper::getCharSet($content);
        $this->assertIsString($charset);
    }

    public function testGetHeadTags()
    {
        $content = '<html>
            <head>
                <title>  站点标题  </title>
                <meta name="keywords" content="larva, support">
                <meta name="description" content="公共支持库">
                <meta name="author" content="Tongle">
            </head>
            <body></body>
        </html>';
        $head = HtmlHelper::getHeadTags($content);
        $this->assertSame('站点标题', $head['title']);
        $this->assertSame('larva, support', $head['keywords']);
        $this->assertSame('公共支持库', $head['description']);
        $this->assertSame('Tongle', $head['metaTags']['author']);
        // keywords/description 从 metaTags 移出
        $this->assertArrayNotHasKey('keywords', $head['metaTags']);
        $this->assertArrayNotHasKey('description', $head['metaTags']);
    }

    public function testGetHeadTagsEmpty()
    {
        $this->assertSame([
            'title' => '',
            'keywords' => '',
            'description' => '',
            'metaTags' => [],
        ], HtmlHelper::getHeadTags(''));
    }

    public function testGetHtmlOutLinkStructure()
    {
        $content = '<a href="https://internal.com/x" rel="nofollow">内链</a>
            <a href="https://external.com/y">外链</a>
            <a href="https://external.com/y">外链重复</a>
            <a href="/relative">相对</a>';
        $result = HtmlHelper::getHtmlOutLink($content, 'internal.com');
        $this->assertSame(3, $result['count']);
        $this->assertSame(2, $result['inlink']); // internal + 相对
        $this->assertSame(1, $result['outlink']);
        $this->assertCount(1, $result['dataList']);

        // 唯一进入 dataList 的是 external.com
        $this->assertSame('external.com', $result['dataList'][0]['host']);
        $this->assertSame('外链', $result['dataList'][0]['title']);
    }

    public function testGetHtmlOutLinkNofollow()
    {
        $content = '<a href="https://a.com/x" rel="nofollow">A</a>
            <a href="https://b.com/y">B</a>';
        $result = HtmlHelper::getHtmlOutLink($content, 'localhost');
        $this->assertSame(1, $result['dataList'][0]['nofollow']);
        $this->assertSame(0, $result['dataList'][1]['nofollow']);
    }

    public function testGetHtmlOutLinkEmpty()
    {
        $this->assertSame([
            'count' => 0,
            'inlink' => 0,
            'outlink' => 0,
            'dataList' => [],
        ], HtmlHelper::getHtmlOutLink('<p>no link</p>', 'localhost'));
    }

    public function testGetHostnamesFromContent()
    {
        $content = '<a href="https://a.com">A</a>
            <a href="https://b.com">B</a>
            <a href="https://a.com">A again</a>
            <a href="/local">local</a>';
        $hosts = HtmlHelper::getHostnames($content);
        $this->assertContains('a.com', $hosts);
        $this->assertContains('b.com', $hosts);
        $this->assertNotContains('local', $hosts);
    }

    public function testGetHostnamesEmpty()
    {
        $this->assertSame([], HtmlHelper::getHostnames('<p>nope</p>'));
    }

    public function testGetSummary()
    {
        $content = '<h1>标题</h1><p>这是<strong>简介</strong>内容，去掉&nbsp;多余&nbsp;空格</p>';
        $summary = HtmlHelper::getSummary($content, 50);
        $this->assertIsString($summary);
        $this->assertStringNotContainsString('<', $summary);
        $this->assertStringContainsString('简介', $summary);
    }

    public function testGetImages()
    {
        $content = '<img src="a.png" /><img src=\'b.jpg\'><img src="c.gif">';
        $this->assertSame(['a.png', 'b.jpg', 'c.gif'], HtmlHelper::getImages($content));
    }

    public function testGetImagesEmpty()
    {
        $this->assertSame([], HtmlHelper::getImages('<p>no images</p>'));
    }

    public function testGetThumb()
    {
        $content = '<img src="first.png"><img src="second.png">';
        $this->assertSame('first.png', HtmlHelper::getThumb($content));
        $this->assertNull(HtmlHelper::getThumb('<p>none</p>'));
    }

    public function testStripHtmlTagsSingle()
    {
        $html = '<p>保留</p><script>删除</script><p>继续</p>';
        $this->assertSame('保留删除继续', HtmlHelper::stripHtmlTags($html, ['p', 'script']));
    }

    public function testStripHtmlTagsArray()
    {
        $html = '<div class="x">A</div><p>P</p>';
        $this->assertSame('AP', HtmlHelper::stripHtmlTags($html, ['div', 'p']));
    }

    public function testStripHtmlImg()
    {
        $html = '<p>文本</p><img src="a.png" /><p>继续</p>';
        $this->assertSame('<p>文本</p><p>继续</p>', HtmlHelper::stripHtmlImg($html));
    }
}
