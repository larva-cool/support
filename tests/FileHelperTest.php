<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\FileHelper;

class FileHelperTest extends TestCase
{
    private string $tmpDir;

    private string $tmpFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpDir = sys_get_temp_dir() . '/larva-support-' . uniqid();
        $this->tmpFile = $this->tmpDir . '/test.txt';
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            $files = glob($this->tmpDir . '/*') ?: [];
            FileHelper::delete($files);
            @rmdir($this->tmpDir);
        }
        parent::tearDown();
    }

    public function testExistsAndMissing()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');

        $this->assertTrue(FileHelper::exists($this->tmpFile));
        $this->assertFalse(FileHelper::missing($this->tmpFile));
        $this->assertFalse(FileHelper::exists($this->tmpDir . '/not-exists.txt'));
    }

    public function testBasenameAndDirname()
    {
        $this->assertSame('file.php', FileHelper::basename('/path/to/file.php'));
        $this->assertSame('/path/to', FileHelper::dirname('/path/to/file.php'));
    }

    public function testExtension()
    {
        $this->assertSame('php', FileHelper::extension('/path/to/file.php'));
        $this->assertSame('txt', FileHelper::extension('note.txt'));
    }

    public function testMakeDirectoryAndReady()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        $this->assertTrue(FileHelper::isDirectory($this->tmpDir));

        // 重复创建（force=true）应不抛错
        FileHelper::makeDirectory($this->tmpDir, 0777, true, true);
        $this->assertTrue(FileHelper::isDirectory($this->tmpDir));

        // readyDirectory 幂等
        FileHelper::readyDirectory($this->tmpDir . '/sub');
        $this->assertTrue(FileHelper::isDirectory($this->tmpDir . '/sub'));
    }

    public function testPutAppendAndRead()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertSame('hello', file_get_contents($this->tmpFile));

        FileHelper::append($this->tmpFile, ' world');
        $this->assertSame('hello world', file_get_contents($this->tmpFile));
    }

    public function testSize()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertSame(5, FileHelper::size($this->tmpFile));
    }

    public function testIsReadableAndWritable()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertTrue(FileHelper::isReadable($this->tmpFile));
        $this->assertTrue(FileHelper::isWritable($this->tmpFile));
        $this->assertTrue(FileHelper::isFile($this->tmpFile));
        $this->assertFalse(FileHelper::isDirectory($this->tmpFile));
    }

    public function testMd5()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertSame(md5_file($this->tmpFile), FileHelper::md5($this->tmpFile));
    }

    public function testLastModified()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertSame(filemtime($this->tmpFile), FileHelper::lastModified($this->tmpFile));
    }

    public function testChmod()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertSame('0644', FileHelper::chmod($this->tmpFile));
        FileHelper::chmod($this->tmpFile, 0600);
        clearstatcache(true, $this->tmpFile);
        $this->assertSame('0600', FileHelper::chmod($this->tmpFile));
        // 还原以避免影响其他测试
        FileHelper::chmod($this->tmpFile, 0644);
    }

    public function testDelete()
    {
        FileHelper::makeDirectory($this->tmpDir, 0777, true);
        FileHelper::put($this->tmpFile, 'hello');
        $this->assertTrue(FileHelper::delete($this->tmpFile));
        $this->assertFalse(FileHelper::exists($this->tmpFile));

        FileHelper::put($this->tmpFile, 'again');
        $this->assertTrue(FileHelper::delete([$this->tmpFile]));
        $this->assertFalse(FileHelper::exists($this->tmpFile));
    }
}
