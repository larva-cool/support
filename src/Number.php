<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support;

use NumberFormatter;

/**
 * 数字金额处理
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Number
{
    /**
     * 转换保留N位数的 Float值
     *
     * @param float $number
     * @param int|null $limit 保留位数
     * @return float
     */
    public static function float(float $number, int $limit = null): float
    {
        $limit = $limit ?? 2;
        return (float) sprintf("%.{$limit}f", $number);
    }

    /**
     * 金额处理(用于计算).
     *
     * @param float    $price
     * @param int|null $limit
     * @return float
     */
    public static function price(float $price, int $limit = null): float
    {
        return static::float(max($price, 0), $limit ?? 2);
    }

    /**
     * 金额处理(用于显示).
     *
     * @param float $price
     * @return string
     */
    public static function priceFormat(float $price): string
    {
        return sprintf('%.2f', max($price, 0));
    }

    /**
     * 将数字格式化成人民币字符串.
     *
     * @param float $price
     * @return string ￥100.00
     */
    public static function cny(float $price): string
    {
        $fmt = NumberFormatter::create('zh_CN', NumberFormatter::CURRENCY);
        return $fmt->format($price);
    }
}
