<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

class SqlHelper
{
    /**
     * 从sql文件获取纯sql语句
     * @param string $sqlFile sql文件路径
     * @param bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀"my_"替换成"me_"
     *         这种前缀替换方法不一定准确，比如正常内容内有跟前缀相同的字符，也会被替换
     * @return array|false|string
     */
    public static function getSqlFromFile(string $sqlFile = '', bool $string = false, array $replace = [])
    {
        if (!FileHelper::exists($sqlFile)) {
            return false;
        }
        $content = file_get_contents($sqlFile);

        return static::parseSql($content, $string, $replace);
    }

    /**
     * 分割sql语句
     * @param string $content sql内容
     * @param bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀my_替换成me_
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    public static function parseSql(string $content = '', bool $string = false, array $replace = [])
    {
        // 被替换的前缀
        $from = '';
        // 要替换的前缀
        $to = '';

        // 替换表前缀
        if (!empty($replace)) {
            $to = current($replace);
            $from = current(array_flip($replace));
        }

        if (empty($content)) {
            return ($string === true) ? '' : [];
        }

        // 纯sql内容
        $pure_sql = [];

        // 多行注释标记
        $comment = false;

        // 按行分割，兼容多个平台
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = explode("\n", trim($content));

        // 循环处理每一行
        foreach ($content as $key => $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }

            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }

            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }

            // 多行注释开始
            if (str_starts_with($line, '/*')) {
                $comment = true;
                continue;
            }

            // 多行注释结束
            if (str_ends_with($line, '*/')) {
                $comment = false;
                continue;
            }

            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }

            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`' . $from, '`' . $to, $line);
            }

            // sql语句
            $pure_sql[] = $line;
        }

        // 只返回一条语句
        if ($string) {
            return implode("", $pure_sql);
        }

        // 以数组形式返回sql语句
        $pure_sql = implode("\n", $pure_sql);
        return explode(";\n", $pure_sql);
    }
}
