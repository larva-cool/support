<?php

namespace Tests;

use Larva\Support\StringHelper;

class StringHelperTest extends TestCase
{
    public function testCamel()
    {
        $this->assertSame('fooBar', StringHelper::camel('FooBar'));
        $this->assertSame('fooBar', StringHelper::camel('FooBar')); // cached
        $this->assertSame('fooBar', StringHelper::camel('foo_bar'));
        $this->assertSame('fooBar', StringHelper::camel('_foo_bar'));
        $this->assertSame('fooBar', StringHelper::camel('_foo_bar_'));
    }

    public function testStudly()
    {
        $this->assertSame('FooBar', StringHelper::studly('fooBar'));
        $this->assertSame('FooBar', StringHelper::studly('_foo_bar'));
        $this->assertSame('FooBar', StringHelper::studly('_foo_bar_'));
        $this->assertSame('FooBar', StringHelper::studly('_foo_bar_'));
    }

    public function testSnake()
    {
        $this->assertSame('laravel_p_h_p_framework', StringHelper::snake('LaravelPHPFramework'));
        $this->assertSame('laravel_php_framework', StringHelper::snake('LaravelPhpFramework'));
        $this->assertSame('laravel php framework', StringHelper::snake('LaravelPhpFramework', ' '));
        $this->assertSame('laravel_php_framework', StringHelper::snake('Laravel Php Framework'));
        $this->assertSame('laravel_php_framework', StringHelper::snake('Laravel    Php      Framework   '));
        // ensure cache keys don't overlap
        $this->assertSame('laravel__php__framework', StringHelper::snake('LaravelPhpFramework', '__'));
        $this->assertSame('laravel_php_framework_', StringHelper::snake('LaravelPhpFramework_', '_'));
        $this->assertSame('laravel_php_framework', StringHelper::snake('laravel php Framework'));
        $this->assertSame('laravel_php_frame_work', StringHelper::snake('laravel php FrameWork'));
        // prevent breaking changes
        $this->assertSame('foo-bar', StringHelper::snake('foo-bar'));
        $this->assertSame('foo-_bar', StringHelper::snake('Foo-Bar'));
        $this->assertSame('foo__bar', StringHelper::snake('Foo_Bar'));
        $this->assertSame('żółtałódka', StringHelper::snake('ŻółtaŁódka'));
    }

    public function testTitle()
    {
        $this->assertSame('Welcome Back', StringHelper::title('welcome back'));
    }

    public function testRandom()
    {
        $this->assertIsString(StringHelper::random(10));
        $this->assertTrue(16 === strlen(StringHelper::random()));
    }

    public function testQuickRandom()
    {
        $this->assertIsString(StringHelper::quickRandom(10));
        $this->assertTrue(16 === strlen(StringHelper::quickRandom()));
    }

    public function testUpper()
    {
        $this->assertSame('USERNAME', StringHelper::upper('username'));
        $this->assertSame('USERNAME', StringHelper::upper('userNaMe'));
    }

    public function testLower()
    {
        $this->assertSame('username', StringHelper::lower('USERNAME'));
        $this->assertSame('username', StringHelper::lower('UserName'));
    }

    public function testUcfirst()
    {
        $this->assertSame('Hello world', StringHelper::ucfirst('hello world'));
        $this->assertSame('A', StringHelper::ucfirst('a'));
    }

    public function testKebab()
    {
        $this->assertSame('laravel-php-framework', StringHelper::kebab('LaravelPhpFramework'));
        $this->assertSame('foo-bar', StringHelper::kebab('fooBar'));
    }

    public function testAfter()
    {
        $this->assertSame('Framework', StringHelper::after('Laravel PHP Framework', 'PHP '));
        $this->assertSame('Laravel PHP Framework', StringHelper::after('Laravel PHP Framework', 'NotFound'));
        $this->assertSame('Laravel PHP Framework', StringHelper::after('Laravel PHP Framework', ''));
    }

    public function testBefore()
    {
        $this->assertSame('Laravel', StringHelper::before('Laravel PHP Framework', ' PHP'));
        $this->assertSame('Laravel PHP Framework', StringHelper::before('Laravel PHP Framework', 'NotFound'));
        $this->assertSame('Laravel PHP Framework', StringHelper::before('Laravel PHP Framework', ''));
    }

    public function testContains()
    {
        $this->assertTrue(StringHelper::contains('Laravel PHP Framework', 'PHP'));
        $this->assertTrue(StringHelper::contains('Laravel PHP Framework', ['Java', 'PHP']));
        $this->assertFalse(StringHelper::contains('Laravel PHP Framework', ['Java', 'Ruby']));
        $this->assertFalse(StringHelper::contains('Laravel PHP Framework', ''));
    }

    public function testStartsWith()
    {
        $this->assertTrue(StringHelper::startsWith('Laravel PHP Framework', 'Laravel'));
        $this->assertTrue(StringHelper::startsWith('Laravel PHP Framework', ['foo', 'Laravel']));
        $this->assertFalse(StringHelper::startsWith('Laravel PHP Framework', 'PHP'));
        $this->assertFalse(StringHelper::startsWith('Laravel PHP Framework', ''));
    }

    public function testEndsWith()
    {
        $this->assertTrue(StringHelper::endsWith('Laravel PHP Framework', 'Framework'));
        $this->assertTrue(StringHelper::endsWith('Laravel PHP Framework', ['Java', 'Framework']));
        $this->assertFalse(StringHelper::endsWith('Laravel PHP Framework', 'Laravel'));
    }

    public function testFinish()
    {
        $this->assertSame('abc/', StringHelper::finish('abc', '/'));
        $this->assertSame('abc/', StringHelper::finish('abc/', '/'));
        $this->assertSame('abc/', StringHelper::finish('abc///', '/'));
    }

    public function testStart()
    {
        $this->assertSame('/abc', StringHelper::start('abc', '/'));
        $this->assertSame('/abc', StringHelper::start('/abc', '/'));
        $this->assertSame('/abc', StringHelper::start('///abc', '/'));
    }

    public function testIs()
    {
        $this->assertTrue(StringHelper::is('foo*', 'foobar'));
        $this->assertTrue(StringHelper::is('*bar', 'foobar'));
        $this->assertTrue(StringHelper::is('foo*bar', 'foo-test-bar'));
        $this->assertTrue(StringHelper::is('foo', 'foo'));
        $this->assertFalse(StringHelper::is('foo*', 'barfoo'));
        $this->assertFalse(StringHelper::is([], 'foo'));
    }

    public function testLength()
    {
        $this->assertSame(6, StringHelper::length('foobar'));
        $this->assertSame(2, StringHelper::length('中国', 'UTF-8'));
        $this->assertSame(2, StringHelper::length('中国'));
    }

    public function testLimit()
    {
        $this->assertSame('Laravel', StringHelper::limit('Laravel', 7));
        $this->assertSame('Lar...', StringHelper::limit('Laravel PHP Framework', 3));
        $this->assertSame('Laravel PHP Framework', StringHelper::limit('Laravel PHP Framework', 100));
        $this->assertSame('Larave***', StringHelper::limit('Laravel PHP Framework', 6, '***'));
    }

    public function testWords()
    {
        $this->assertSame('Hello world...', StringHelper::words('Hello world foo bar baz', 2));
        $this->assertSame('Hello world', StringHelper::words('Hello world', 100));
    }

    public function testParseCallback()
    {
        $this->assertSame(['Class', 'method'], StringHelper::parseCallback('Class@method'));
        $this->assertSame(['Class', 'method'], StringHelper::parseCallback('Class@method', 'default'));
        $this->assertSame(['Class', 'default'], StringHelper::parseCallback('Class', 'default'));
    }

    public function testSubstr()
    {
        $this->assertSame('def', StringHelper::substr('abcdef', 3));
        $this->assertSame('de', StringHelper::substr('abcdef', 3, 2));
        $this->assertSame('中国', StringHelper::substr('中国abc', 0, 2));
    }

    public function testBasename()
    {
        $this->assertSame('file.php', StringHelper::basename('/path/to/file.php'));
        $this->assertSame('file', StringHelper::basename('/path/to/file.php', '.php'));
        $this->assertSame('file.php', StringHelper::basename('\\path\\to\\file.php'));
    }

    public function testReplaceArray()
    {
        $this->assertSame('Hello Tom and Jerry', StringHelper::replaceArray('?', ['Tom', 'Jerry'], 'Hello ? and ?'));
        $this->assertSame('no match', StringHelper::replaceArray('?', ['Tom'], 'no match'));
    }

    public function testReplaceFirst()
    {
        $this->assertSame('foo qux bar', StringHelper::replaceFirst('bar', 'qux', 'foo bar bar'));
        $this->assertSame('foo bar bar', StringHelper::replaceFirst('not', 'qux', 'foo bar bar'));
        $this->assertSame('foo bar bar', StringHelper::replaceFirst('', 'qux', 'foo bar bar'));
    }

    public function testReplaceLast()
    {
        $this->assertSame('foo bar qux', StringHelper::replaceLast('bar', 'qux', 'foo bar bar'));
        $this->assertSame('foo bar bar', StringHelper::replaceLast('not', 'qux', 'foo bar bar'));
    }

    public function testCreateUuid()
    {
        $uuid = StringHelper::createUuid();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $uuid);

        $prefixed = StringHelper::createUuid('usr_');
        $this->assertStringStartsWith('usr_', $prefixed);
        $this->assertMatchesRegularExpression('/^usr_[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $prefixed);
    }

    public function testRandomInteger()
    {
        $code = StringHelper::randomInteger(6);
        $this->assertIsString($code);
        $this->assertSame(6, strlen($code));
        $this->assertMatchesRegularExpression('/^\d+$/', $code);
    }

    public function testBase62RoundTrip()
    {
        $original = 'Hello, World! 123';
        $encoded = StringHelper::base62Encode($original);
        $this->assertNotEmpty($encoded);
        $decoded = StringHelper::base62Decode($encoded);
        $this->assertSame($original, $decoded);
    }

    public function testBase62DecodeInvalidInput()
    {
        $this->assertFalse(StringHelper::base62Decode('!!!'));
    }
}
