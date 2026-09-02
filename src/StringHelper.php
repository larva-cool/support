<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

use Larva\Support\Exception\RuntimeException;

/**
 * 字符串助手
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StringHelper
{
    public const BASE_62 = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static array $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static array $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static array $studlyCache = [];

    /**
     * 创建 UUID
     * @param string $prefix
     * @return string
     */
    public static function createUuid(string $prefix = ""): string
    {
        $chars = md5(uniqid((string)mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-'
            . substr($chars, 8, 4) . '-'
            . substr($chars, 12, 4) . '-'
            . substr($chars, 16, 4) . '-'
            . substr($chars, 20, 12);
        return $prefix . $uuid;
    }

    /**
     * 将值转换为驼峰
     *
     * @param string $value
     *
     * @return string
     */
    public static function camel(string $value): string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }
        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * 生成一个更真实的“随机”字符串
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function random(int $length = 16): string
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = static::randomBytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }

    /**
     * Generate a more truly "random" bytes.
     *
     * @param int $length
     *
     * @return string
     *
     * @throws RuntimeException
     *
     * @codeCoverageIgnore
     *
     * @throws \Exception
     */
    public static function randomBytes(int $length = 16): string
    {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($length);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if (false === $bytes || false === $strong) {
                throw new RuntimeException('Unable to generate random string.');
            }
        } else {
            throw new RuntimeException('OpenSSL extension is required for PHP 5 users.');
        }

        return $bytes;
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param int $length
     *
     * @return string
     */
    public static function quickRandom(int $length = 16): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * 生成一个随机的数字字符串
     * @param int $length
     * @return string
     */
    public static function randomInteger(int $length = 6): string
    {
        $letters = '678906789067890678906';
        $vowels = '12345';
        $code = '';
        for ($i = 0; $i < $length; ++$i) {
            if ($i % 2 && mt_rand(0, 10) > 2 || !($i % 2) && mt_rand(0, 10) > 9) {
                $code .= $vowels[mt_rand(0, 4)];
            } else {
                $code .= $letters[mt_rand(0, 20)];
            }
        }
        return $code;
    }

    /**
     * 将字符串转换为大写
     *
     * @param string $value
     * @return string
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * 将字符串转换为标题
     *
     * @param string $value
     * @return string
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * 将字符串转换为snake格式
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * 返回字符串中给定某个值之后的所有内容
     *
     * @param string $subject
     * @param string $search
     *
     * @return string
     */
    public static function after(string $subject, string $search): string
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * 返回字符串中给定某个值之前的所有内容
     *
     * @param string $subject
     * @param string $search
     * @return string
     */
    public static function before(string $subject, string $search): string
    {
        return $search === '' ? $subject : explode($search, $subject)[0];
    }

    /**
     * 判断字符串是否包含指定的子字符串
     *
     * @param string $haystack 要被检查的字符串
     * @param string|array $needles 要搜索的字符串
     * @return bool
     */
    public static function contains(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断字符串是否以给定的子字符串开头
     *
     * @param string $haystack 要被检查的字符串
     * @param string|array $needles 要搜索的字符串
     *
     * @return bool
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断字符串是否以给定的子字符串结束
     *
     * @param string $haystack 要被检查的字符串
     * @param string|array $needles 要搜索的字符串
     * @return bool
     */
    public static function endsWith(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $cap
     * @return string
     */
    public static function finish(string $value, string $cap): string
    {
        $quoted = preg_quote($cap, '/');
        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }

    /**
     * 判断字符串是否与给定的表达式匹配
     *
     * @param string|array $pattern
     * @param string $value
     * @return bool
     */
    public static function is($pattern, string $value): bool
    {
        $patterns = is_array($pattern) ? $pattern : (array)$pattern;
        if (empty($patterns)) {
            return false;
        }
        foreach ($patterns as $pattern) {
            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * 将字符串转换为kebab格式
     *
     * @param string $value
     * @return string
     */
    public static function kebab(string $value): string
    {
        return static::snake($value, '-');
    }

    /**
     * 返回字符串的长度
     *
     * @param string $value
     * @param null $encoding
     * @return int
     */
    public static function length(string $value, $encoding = null): int
    {
        if ($encoding !== null) {
            return mb_strlen($value, $encoding);
        }
        return mb_strlen($value);
    }

    /**
     * 限制字符串中的字符数
     *
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    /**
     * 将字符串转换为小写
     *
     * @param string $value
     * @return string
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * 限制字符串中单词的数量
     *
     * @param string $value
     * @param int $words
     * @param string $end
     * @return string
     */
    public static function words(string $value, int $words = 100, string $end = '...'): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);
        if (!isset($matches[0]) || static::length($value) === static::length($matches[0])) {
            return $value;
        }
        return rtrim($matches[0]) . $end;
    }

    /**
     * 将Class@method样式回调解析为类和方法
     *
     * @param string $callback
     * @param string|null $default
     * @return array
     */
    public static function parseCallback(string $callback, string $default = null): array
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * 返回由起始和长度参数指定的字符串部分
     *
     * @param string $string
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function substr(string $string, int $start, int $length = null): string
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * 确保字符串只是由某个特定的值开始
     *
     * @param string $value
     * @param string $prefix
     * @return string
     */
    public static function start(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');
        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }


    /**
     * 字符串第一个字符大写
     *
     * @param string $string
     * @return string
     */
    public static function ucfirst(string $string): string
    {
        return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
    }

    /**
     * 字符串转码
     *
     * @param string $string
     * @param string $to
     * @param string $from
     * @return string
     */
    public static function encoding(string $string, $to = 'utf-8', $from = 'gb2312'): string
    {
        return mb_convert_encoding($string, $to, $from);
    }

    /**
     * 将字符串转化为单词开头字母大写的格式
     *
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $key = $value;
        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Returns the trailing name component of a path.
     * This method is similar to the php function `basename()` except that it will
     * treat both \ and / as directory separators, independent of the operating system.
     * This method was mainly created to work on php namespaces. When working with real
     * file paths, php's `basename()` should work fine for you.
     * Note: this method is not aware of the actual filesystem, or path components such as "..".
     *
     * @param string $path A path string.
     * @param string $suffix If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename(string $path, $suffix = ''): string
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) === $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }

    /**
     * 用数组顺序替换字符串中的指定值
     *
     * @param string $search
     * @param array $replace
     * @param string $subject
     * @return string
     */
    public static function replaceArray(string $search, array $replace, string $subject): string
    {
        foreach ($replace as $value) {
            $subject = static::replaceFirst($search, $value, $subject);
        }
        return $subject;
    }

    /**
     * 替换字符串中第一次出现的值
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    /**
     * 替换字符串中最后一次出现的值
     *
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        $position = strrpos($subject, $search);
        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }
        return $subject;
    }

    /**
     * Base62 编码
     * @param string $data
     * @return string
     */
    public static function base62Encode(string $data): string
    {
        $data = strval($data);
        $base62 = str_split(static::BASE_62);
        $len = strlen($data);
        $i = 0;
        $tmpArr = [];
        while ($i < $len) {
            $tmp = str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
            $temp = str_split($tmp, 4);
            $tmpArr = array_merge($tmpArr, $temp);
            ++$i;
        }
        $result = '';
        $i = 0;
        foreach ($tmpArr as $arr) {
            $temp = bindec($arr) * 4 + $i % 2;
            $result .= $base62[$temp];
            ++$i;
        }
        return $result;
    }

    /**
     * Base62 解码
     * @param string $data
     * @return bool|string
     */
    public static function base62Decode(string $data)
    {
        $data = strval($data);
        $base62 = str_split(static::BASE_62);
        $base62Arr = array_flip($base62);
        if (!preg_match('/[a-zA-Z0-9]+/', $data)) {
            return false;
        }
        $len = strlen($data);
        $i = 0;
        $tempArr = [];
        while ($i < $len) {
            $temp = decbin(($base62Arr[$data[$i]] - $i % 2) / 4);
            $tempArr[] = str_pad($temp, 4, '0', STR_PAD_LEFT);
            ++$i;
        }
        $result = '';
        $tempArr = array_chunk($tempArr, 2);
        foreach ($tempArr as $arr) {
            $result .= chr(bindec(join('', $arr)));
        }
        return $result;
    }

    /**
     * Encodes string into "Base 64 Encoding with URL and Filename Safe Alphabet" (RFC 4648).
     *
     * > Note: Base 64 padding `=` may be at the end of the returned string.
     * > `=` is not transparent to URL encoding.
     *
     * @see https://tools.ietf.org/html/rfc4648#page-7
     * @param string $input the string to encode.
     * @return string encoded string.
     */
    public static function base64UrlEncode(string $input): string
    {
        return strtr(base64_encode($input), '+/', '-_');
    }

    /**
     * Decodes "Base 64 Encoding with URL and Filename Safe Alphabet" (RFC 4648).
     *
     * @see https://tools.ietf.org/html/rfc4648#page-7
     * @param string $input encoded string.
     * @return string decoded string.
     */
    public static function base64UrlDecode(string $input): string
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * 安全地将浮点数强制转换为与当前语言环境无关的字符串。
     *
     * The decimal separator will always be `.`.
     * @param float|int $number a floating point number or integer.
     * @return string the string representation of the number.
     */
    public static function floatToString($number): string
    {
        // . and , are the only decimal separators known in ICU data,
        // so its safe to call str_replace here
        return str_replace(',', '.', (string)$number);
    }

    /**
     * Converts an object to its string representation. If the object is an array, will glue the array elements togeter
     * with the $glue param. Otherwise will cast the object to a string.
     *
     * @param mixed $object The object to convert to a string.
     * @param string $glue The glue to use if the object is an array.
     * @return string The string representation of the object.
     */
    public static function toString($object, string $glue = ','): string
    {
        if (is_scalar($object) || (is_object($object) && method_exists($object, '__toString'))) {
            return (string)$object;
        }

        if (is_array($object) || $object instanceof \IteratorAggregate) {
            $stringValues = [];

            foreach ($object as $value) {
                if (($value = static::toString($value, $glue)) !== '') {
                    $stringValues[] = $value;
                }
            }

            return implode($glue, $stringValues);
        }

        return '';
    }
}
