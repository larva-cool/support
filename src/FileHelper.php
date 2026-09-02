<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use ErrorException;
use RuntimeException;
use Symfony\Component\Mime\MimeTypes;

/**
 * 文件助手
 * @author Tongle Xu <xutongle@gmail.com>
 */
class FileHelper
{
    /**
     * 判断文件或目录是否存在
     *
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * 判断文件或目录是否不存在
     *
     * @param string $path
     * @return bool
     */
    public static function missing(string $path): bool
    {
        return !static::exists($path);
    }

    /**
     * 获取文件名
     *
     * @param string $path
     * @return string
     */
    public static function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * 获取父目录
     *
     * @param string $path
     * @return string
     */
    public static function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * 获取文件后缀
     *
     * @param string $path
     * @return string
     */
    public static function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type of a given file.
     *
     * @param string $path
     * @return string
     */
    public static function type(string $path): string
    {
        return filetype($path);
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param string $path
     * @return string|false
     */
    public static function mimeType(string $path)
    {
        return finfo_file(finfo_open(\FILEINFO_MIME_TYPE), $path);
    }

    /**
     * 返回指定文件的大小
     *
     * @param string $path
     * @return int 返回文件大小的字节数
     */
    public static function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * 获取格式化后的文件大小
     * @param string $path
     * @return string
     */
    public static function sizeFormat(string $path): string
    {
        $size = static::size($path);
        $sizes = [" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"];
        if ($size == 0) {
            return 'N/A';
        } else {
            return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i];
        }
    }

    /**
     * 获取文件最后修改的时间戳
     *
     * @param string $path
     * @return int
     */
    public static function lastModified(string $path): int
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param string $directory
     * @return bool
     */
    public static function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }

    /**
     * Determine if the given path is readable.
     *
     * @param string $path
     * @return bool
     */
    public static function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param string $path
     * @return bool
     */
    public static function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param string $file
     * @return bool
     */
    public static function isFile(string $file): bool
    {
        return is_file($file);
    }

    /**
     * Get the MD5 hash of the file at the given path.
     *
     * @param string $path
     * @return string
     */
    public static function md5(string $path): string
    {
        return md5_file($path);
    }

    /**
     * Get the sha1 hash of the file at the given path.
     *
     * @param string $path
     * @return string
     */
    public static function sha1(string $path): string
    {
        return sha1_file($path);
    }

    /**
     * Get or set UNIX mode of a file or directory.
     *
     * @param string $path
     * @param int|null $mode
     * @return bool|string
     */
    public static function chmod(string $path, int $mode = null)
    {
        if ($mode) {
            return chmod($path, $mode);
        }
        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */
    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }
        return mkdir($path, $mode, $recursive);
    }

    /**
     * 准备目录
     * @param string $path
     * @param int $mode
     */
    public static function readyDirectory(string $path, int $mode = 0755)
    {
        if (!static::isDirectory($path)) {
            static::makeDirectory($path, $mode, true);
        }
    }

    /**
     * 写入内容到文件
     *
     * @param string $path
     * @param mixed $contents
     * @param bool $lock
     * @return int|bool
     */
    public static function put(string $path, $contents, bool $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Append to a file.
     *
     * @param string $path
     * @param mixed $data
     * @return int|false
     */
    public static function append(string $path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     * @return bool
     */
    public static function delete($paths): bool
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (!@unlink($path)) {
                    $success = false;
                }
            } catch (ErrorException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Guess the file extension from the mime-type of a given file.
     *
     * @param string $path
     * @return string|null
     */
    public static function guessExtension(string $path): ?string
    {
        if (!class_exists(MimeTypes::class)) {
            throw new RuntimeException(
                'To enable support for guessing extensions, please install the symfony/mime package.'
            );
        }
        return (new MimeTypes())->getExtensions(static::mimeType($path))[0] ?? null;
    }

    /**
     * Return steam extension.
     *
     * @param string $stream
     * @return string|false
     */
    public static function getStreamExtension($stream)
    {
        if (!class_exists(MimeTypes::class)) {
            throw new RuntimeException(
                'To enable support for guessing extensions, please install the symfony/mime package.'
            );
        }
        $fileInfo = new \finfo(FILEINFO_MIME);
        $mime = strstr($fileInfo->buffer($stream), ';', true);
        return (new MimeTypes())->getExtensions($mime)[0] ?? null;
    }
}
