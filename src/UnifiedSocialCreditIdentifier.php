<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support;

/**
 * 统一社会信用代码
 *
 * 用于校验、解析、生成 18 位统一社会信用代码。
 * 编码规则参考 GB 32100-2015。
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class UnifiedSocialCreditIdentifier
{
    /**
     * 统一社会信用代码最大长度。
     */
    public const CHINA_CREDIT_CODE_MAX_LENGTH = 18;

    /**
     * 登记管理部门代码与名称映射。
     *
     * 第 1 位：登记管理部门代码
     */
    private const MANAGE_CODES = [
        '1' => '机构编制',
        '5' => '民政',
        '9' => '工商',
        'Y' => '其他',
    ];

    /**
     * 机构类别代码与名称映射。
     *
     * 第 2 位：机构类别代码
     */
    private const TYPE_CODES = [
        '1' => '企业',
        '2' => '个体工商户',
        '3' => '农民专业合作社',
    ];

    /**
     * 每位加权因子（共 17 位）。
     */
    private const POWER = [1, 3, 9, 27, 19, 26, 16, 17, 20, 29, 25, 13, 8, 24, 10, 30, 28];

    /**
     * 字符到数值的映射（包含 0-9 与不含 I、O、Z、S、V 的 18 位字母）。
     */
    private const TRANSFORMATION = [
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
        'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13,
        'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17,
        'J' => 18, 'K' => 19, 'L' => 20, 'M' => 21,
        'N' => 22, 'P' => 23, 'Q' => 24, 'R' => 25,
        'T' => 26, 'U' => 27, 'W' => 28, 'X' => 29, 'Y' => 30,
    ];

    /**
     * 校验合法字符正则：数字 0-9 + 大写字母（除 I O Z S V 外）。
     */
    private const CHARSET_PATTERN = '/^[0-9A-HJ-NPQRTUWXY]+$/';

    /**
     * 获取机构信息
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return array|false 成功返回包含 manage / type / 行政区划等信息的数组；失败返回 false
     */
    public static function getInfo(string $creditCode): bool|array
    {
        if (!static::validate($creditCode)) {
            return false;
        }
        $info = [
            'manage' => substr($creditCode, 0, 1),
            'type' => substr($creditCode, 1, 1),
            'province_code' => static::getProvinceCodeByCreditIdentifier($creditCode),
            'city_code' => static::getCityCodeByCreditIdentifier($creditCode),
            'district_code' => static::getDistrictCodeByCreditIdentifier($creditCode),
        ];
        $info['province'] = IDCard::$locationCodes[$info['province_code']] ?? '';
        $info['city'] = IDCard::$locationCodes[$info['city_code']] ?? '';
        $info['district'] = IDCard::$locationCodes[$info['district_code']] ?? '';
        return $info;
    }

    /**
     * 生成随机的统一社会信用代码
     *
     * @param string $area 6 位行政区划代码，例如 '110000'
     * @return string 18 位统一社会信用代码
     * @throws \InvalidArgumentException 当 $area 不是 6 位数字时抛出
     */
    public static function generate(string $area): string
    {
        if (strlen($area) !== 6 || !ctype_digit($area)) {
            throw new \InvalidArgumentException('Area code must be a 6-digit string.');
        }
        // 第 1 位：登记管理部门代码，9 = 工商
        // 第 2 位：机构类别代码，1 = 企业
        // 第 3-8 位：登记管理机关行政区划码
        // 第 9-17 位：登记管理机关内部顺序码（这里使用日期 + 1 位随机数凑齐 9 位）
        $code17 = '91' . $area . date('Ymd') . sprintf('%01d', random_int(1, 9));
        if (strlen($code17) !== 17) {
            // 理论上不会发生：date('Ymd') 始终为 8 位；保留以防极端环境
            throw new \RuntimeException('Failed to build 17-digit base code.');
        }
        $iSum17 = static::getPowerSum(str_split($code17));
        return $code17 . static::getCheckCode($iSum17);
    }

    /**
     * 获取机构所在省代码
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return string 6 位省代码，例如 '110000'
     */
    public static function getProvinceCodeByCreditIdentifier(string $creditCode): string
    {
        return substr($creditCode, 2, 2) . '0000';
    }

    /**
     * 获取机构所在省名称
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return string 省名称，未匹配返回空串
     */
    public static function getProvinceByCreditIdentifier(string $creditCode): string
    {
        $provinceCode = static::getProvinceCodeByCreditIdentifier($creditCode);
        return IDCard::$locationCodes[$provinceCode] ?? '';
    }

    /**
     * 获取机构所在市代码
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return string 6 位市代码，例如 '410100'
     */
    public static function getCityCodeByCreditIdentifier(string $creditCode): string
    {
        return substr($creditCode, 2, 4) . '00';
    }

    /**
     * 获取机构所在市名称
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return string 市名称，未匹配返回空串
     */
    public static function getCityByCreditIdentifier(string $creditCode): string
    {
        $cityCode = static::getCityCodeByCreditIdentifier($creditCode);
        return IDCard::$locationCodes[$cityCode] ?? '';
    }

    /**
     * 获取机构所在县（区）代码
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return string 6 位县（区）代码，例如 '410105'
     */
    public static function getDistrictCodeByCreditIdentifier(string $creditCode): string
    {
        return substr($creditCode, 2, 6);
    }

    /**
     * 获取机构所在县（区）名称
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return string 县（区）名称，未匹配返回空串
     */
    public static function getDistrictByCreditIdentifier(string $creditCode): string
    {
        $areaCode = static::getDistrictCodeByCreditIdentifier($creditCode);
        return IDCard::$locationCodes[$areaCode] ?? '';
    }

    /**
     * 验证统一社会信用代码是否合法
     *
     * @param string $creditCode 18 位统一社会信用代码
     * @return bool 合法返回 true，否则 false
     */
    public static function validate(string $creditCode): bool
    {
        if (strlen($creditCode) !== static::CHINA_CREDIT_CODE_MAX_LENGTH) {
            return false;
        }
        // 字符集合法性（不含 I、O、Z、S、V）
        if (!preg_match(static::CHARSET_PATTERN, $creditCode)) {
            return false;
        }
        $code17 = substr($creditCode, 0, 17);
        $code18 = substr($creditCode, 17, 1);
        $iSum17 = static::getPowerSum(str_split($code17));
        $expected = static::getCheckCode($iSum17);
        return $expected === $code18;
    }

    /**
     * 将 power 和值与 31 取模获得余数进行校验码判断
     *
     * @param int $iSum 加权因子与字符数值乘积之和
     * @return string 校验位字符（0-9 或 A-Z 中合法字符）
     */
    private static function getCheckCode(int $iSum): string
    {
        $mod = $iSum % 31;
        $code = $mod === 0 ? 0 : 31 - $mod;
        // 反查 transformation 数组找到对应的字符（含数字与字母）
        foreach (static::TRANSFORMATION as $char => $value) {
            if ($value === $code) {
                return (string)$char;
            }
        }
        return '0';
    }

    /**
     * 将每位和对应位的加权因子相乘之后，再得到和值
     *
     * @param array $iArr 17 位字符数组
     * @return int 加权和
     */
    private static function getPowerSum(array $iArr): int
    {
        $iSum = 0;
        $arrLen = count($iArr);
        $powerLen = count(static::POWER);
        if ($arrLen !== $powerLen) {
            return 0;
        }
        for ($i = 0; $i < $arrLen; $i++) {
            $char = (string)$iArr[$i];
            $value = static::TRANSFORMATION[$char] ?? null;
            if ($value === null) {
                return 0;
            }
            $iSum += $value * static::POWER[$i];
        }
        return $iSum;
    }
}
