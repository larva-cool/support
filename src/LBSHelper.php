<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Support;

/**
 * Class LBS
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class LBSHelper
{
    /**
     * @var double 地球半径
     */
    public static float $radius = 6378245.0;

    /**
     * @var double 卫星椭球坐标投影到平面地图坐标系的投影因子。
     */
    public static float $X_PI = 52.35987755982988;

    /**
     * @var double 偏心率
     */
    public static float $EE = 0.00669342162296594323;

    /**
     * 高德的矩形补全
     * @param string $rectangle 116.0119343,39.66127144;116.7829835,40.2164962  所在城市范围的左下右上对标对
     * @return array [[lon,lat],[lon,lat]]
     */
    public static function getAMAPRectangle(string $rectangle): array
    {
        list($bottomLeft, $topRight) = explode(';', $rectangle);
        $bottomLeft = explode(',', $bottomLeft);
        $topRight = explode(',', $topRight);
        $topLeft = [$bottomLeft[0], $topRight[1]];
        $bottomRight = [$topRight[0], $bottomLeft[1]];
        return [
            $topLeft, $bottomLeft, $topRight, $bottomRight
        ];
    }

    /**
     * 获取多个经纬度的中心点
     * @param array $points [[lon,lat],[lon,lat]]
     * @return array|bool [lon,lat]
     *
     * // 测试数据
     * $data = array(
     * array(76.322333,45.849382),
     * array(45.843543, 75.324143),
     * array(45.765744, 76.543223),
     * array(45.784234, 74.542335)
     * );
     *
     * print_r(GetCenterFromDegrees($data));
     * // Array ( [0] => 45.813538469271 [1] => 75.682996448603 )
     */
    public static function getCenterFromDegrees(array $points)
    {
        $numCoords = count($points);
        $X = 0.0;
        $Y = 0.0;
        $Z = 0.0;
        foreach ($points as $point) {
            $lon = deg2rad((float)$point[0]);
            $lat = deg2rad((float)$point[1]);
            $X += cos($lat) * cos($lon);
            $Y += cos($lat) * sin($lon);
            $Z += sin($lat);
        }

        $X /= $numCoords;
        $Y /= $numCoords;
        $Z /= $numCoords;

        $lon = atan2($Y, $X);
        $lat = atan2($Z, sqrt($X * $X + $Y * $Y));

        return [round(rad2deg($lon), 6), round(rad2deg($lat), 6)];
    }

    /**
     * 计算两个经纬度之间的距离
     * @param float|string $longitude1
     * @param float|string $latitude1
     * @param float|string $longitude2
     * @param float|string $latitude2
     * @param float $radius 星球半径 KM
     * @return double
     */
    public static function distance($longitude1, $latitude1, $longitude2, $latitude2, float $radius = 6378.137)
    {
        $rad = M_PI / 180.0;
        $longitude1 = floatval($longitude1) * $rad;
        $latitude1 = floatval($latitude1) * $rad;
        $longitude2 = floatval($longitude2) * $rad;
        $latitude2 = floatval($latitude2) * $rad;
        $theta = $longitude2 - $longitude1;
        $dist = acos(sin($latitude1) * sin($latitude2) + cos($latitude1) * cos($latitude2) * cos($theta));
        if ($dist < 0) {
            $dist += M_PI;
        }
        return $dist * $radius;
    }

    /**
     * 附近范围
     * //(`latitude` >= minLat) AND (`latitude` <=maxLat) AND (`longitude` >= minLng) AND (`longitude` <= maxLng)
     * @param float $latitude 纬度
     * @param float $longitude 经度
     * @param float $radius 半径范围(单位：米)
     * @return array
     */
    public static function getAround(float $longitude, float $latitude, float $radius): array
    {
        $degree = (24901 * 1609) / 360.0;
        $dpmLat = 1 / $degree;
        $radiusLat = $dpmLat * $radius;
        $minLat = $latitude - $radiusLat;
        $maxLat = $latitude + $radiusLat;
        $mpdLng = $degree * cos($latitude * (M_PI / 180.0));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng * $radius;
        $minLng = $longitude - $radiusLng;
        $maxLng = $longitude + $radiusLng;
        return ['minLat' => $minLat, 'maxLat' => $maxLat, 'minLng' => $minLng, 'maxLng' => $maxLng];
    }

    /**
     * WGS84转GCJ02(GPS转火星)
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public static function WGS84ToGCJ02(float $longitude, float $latitude): array
    {
        if (self::isChina($longitude, $latitude)) {
            return [$longitude, $latitude];
        } else {
            $dlat = static::transFormLatitude($longitude - 105.0, $latitude - 35.0);
            $dlng = static::transFormLongitude($longitude - 105.0, $latitude - 35.0);
            $radLat = $latitude / 180.0 * M_PI;
            $magic = sin($radLat);
            $magic = 1 - static::$EE * $magic * $magic;
            $sqrtMagic = sqrt($magic);
            $dlat = ($dlat * 180.0) / ((static::$radius * (1 - static::$EE)) / ($magic * $sqrtMagic) * M_PI);
            $dlng = ($dlng * 180.0) / (static::$radius / $sqrtMagic * cos($radLat) * M_PI);
            $magicLat = $latitude + $dlat;
            $magicLon = $longitude + $dlng;
            return [$magicLon, $magicLat];
        }
    }

    /**
     * GCJ02 转换为 WGS84 (GPS转火星)
     * @param float $longitude
     * @param float $latitude
     * @return array(lng, lat);
     */
    public static function GCJ02ToWGS84(float $longitude, float $latitude): array
    {
        if (static::isChina($longitude, $latitude)) {
            return [$longitude, $latitude];
        } else {
            $dlat = static::transFormLatitude($longitude - 105.0, $latitude - 35.0);
            $dlng = static::transFormLongitude($longitude - 105.0, $latitude - 35.0);
            $radLat = $latitude / 180.0 * M_PI;
            $magic = sin($radLat);
            $magic = 1 - static::$EE * $magic * $magic;
            $sqrtMagic = sqrt($magic);
            $dlat = ($dlat * 180.0) / ((static::$radius * (1 - static::$EE)) / ($magic * $sqrtMagic) * M_PI);
            $dlng = ($dlng * 180.0) / (static::$radius / $sqrtMagic * cos($radLat) * M_PI);
            $magicLat = $latitude + $dlat;
            $magicLng = $longitude + $dlng;
            return [$longitude * 2 - $magicLng, $latitude * 2 - $magicLat];
        }
    }

    /**
     * 百度坐标系 (BD-09) 与 火星坐标系 (GCJ-02)的转换
     * 即 百度 转 谷歌、高德
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public static function BD09ToGCJ02(float $longitude, float $latitude): array
    {
        $x = $longitude - 0.0065;
        $y = $latitude - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * static::$X_PI);
        $theta = atan2($y, $x) - 0.000003 * cos($x * static::$X_PI);
        $gg_lng = $z * cos($theta);
        $gg_lat = $z * sin($theta);
        return [$gg_lng, $gg_lat];
    }

    /**
     * 获取 MongoDB 位置格式
     * @param float $longitude
     * @param float $latitude
     * @return array
     */
    public static function getMongoGeometry(float $longitude, float $latitude): array
    {
        return [
            'type' => 'Point',
            'coordinates' => [doubleval($longitude), doubleval($latitude)]
        ];
    }

    /**
     * 获取 MongoDB 位置格式
     * @param float|string $longitude
     * @param float|string $latitude
     * @return array
     */
    public static function getMongo2d($longitude, $latitude): array
    {
        return [doubleval($longitude), doubleval($latitude)];
    }

    /**
     * GCJ-02 转换为 BD-09  （火星坐标系 转百度即谷歌、高德 转 百度）
     * @param float $longitude
     * @param float $latitude
     * @return array (lon,lat);
     */
    public static function GCJ02ToBD09(float $longitude, float $latitude): array
    {
        $z = sqrt($longitude * $longitude + $latitude * $latitude) + 0.00002 * sin($latitude * static::$X_PI);
        $theta = atan2($latitude, $longitude) + 0.000003 * cos($longitude * static::$X_PI);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return [$bd_lon, $bd_lat];
    }

    /**
     * 判断是否在国内，不在国内则不做偏移
     * @param float $longitude
     * @param float $latitude
     * @return bool
     */
    private static function isChina(float $longitude, float $latitude): bool
    {
        // 经度或纬度任一落在范围外即视为不在国内
        return !($longitude < 72.004 || $longitude > 137.8347
            || $latitude < 0.8293 || $latitude > 55.8271);
    }

    /**
     * 转换纬度
     *
     * @param float $longitude
     * @param float $latitude
     * @return float|int
     */
    private static function transFormLatitude(float $longitude, float $latitude): float|int
    {
        $ret = -100.0 + 2.0 * $longitude + 3.0 * $latitude + 0.2 * $latitude * $latitude + 0.1 * $longitude * $latitude + 0.2 * sqrt(abs($longitude));
        $ret += (20.0 * sin(6.0 * $longitude * M_PI) + 20.0 * sin(2.0 * $longitude * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($latitude * M_PI) + 40.0 * sin($latitude / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($latitude / 12.0 * M_PI) + 320 * sin($latitude * M_PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    /**
     * 转换经度
     *
     * @param float $longitude
     * @param float $latitude
     * @return float|int
     */
    private static function transFormLongitude(float $longitude, float $latitude): float|int
    {
        $ret = 300.0 + $longitude + 2.0 * $latitude + 0.1 * $longitude * $longitude + 0.1 * $longitude * $latitude + 0.1 * sqrt(abs($longitude));
        $ret += (20.0 * sin(6.0 * $longitude * M_PI) + 20.0 * sin(2.0 * $longitude * M_PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($longitude * M_PI) + 40.0 * sin($longitude / 3.0 * M_PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($longitude / 12.0 * M_PI) + 300.0 * sin($longitude / 30.0 * M_PI)) * 2.0 / 3.0;
        return $ret;
    }
}
