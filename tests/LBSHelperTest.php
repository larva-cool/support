<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Tests;

use Larva\Support\LBSHelper;

class LBSHelperTest extends TestCase
{
    public function testDistance()
    {
        // 北京天安门到上海外滩的球面距离大约 1067 km
        $distance = LBSHelper::distance(116.397128, 39.916527, 121.490317, 31.236305);
        $this->assertIsFloat($distance);
        $this->assertEqualsWithDelta(1067.0, $distance, 30.0);

        // 同一坐标点距离应为 0
        $this->assertSame(0.0, LBSHelper::distance(116.0, 39.0, 116.0, 39.0));
    }

    public function testGetAround()
    {
        $around = LBSHelper::getAround(116.0, 39.0, 1000.0);
        $this->assertArrayHasKey('minLat', $around);
        $this->assertArrayHasKey('maxLat', $around);
        $this->assertArrayHasKey('minLng', $around);
        $this->assertArrayHasKey('maxLng', $around);
        $this->assertLessThan(39.0, $around['minLat']);
        $this->assertGreaterThan(39.0, $around['maxLat']);
        $this->assertLessThan(116.0, $around['minLng']);
        $this->assertGreaterThan(116.0, $around['maxLng']);
    }

    public function testWGS84ToGCJ02()
    {
        // 中国境内坐标不做偏移
        [$lng, $lat] = LBSHelper::WGS84ToGCJ02(116.397128, 39.916527); // 北京天安门
        $this->assertSame(116.397128, $lng);
        $this->assertSame(39.916527, $lat);
    }

    public function testWGS84ToGCJ02OutsideChina()
    {
        // 中国境外坐标会被偏移
        [$lng, $lat] = LBSHelper::WGS84ToGCJ02(-73.9857, 40.7484); // 纽约
        $this->assertNotSame(-73.9857, $lng);
        $this->assertNotSame(40.7484, $lat);
        $this->assertIsFloat($lng);
        $this->assertIsFloat($lat);
    }

    public function testGCJ02ToWGS84()
    {
        // 境内坐标 GCJ02 -> WGS84 不偏移
        [$wgsLng, $wgsLat] = LBSHelper::GCJ02ToWGS84(116.397128, 39.916527);
        $this->assertSame(116.397128, $wgsLng);
        $this->assertSame(39.916527, $wgsLat);
    }

    public function testBD09ToGCJ02()
    {
        // 北京天安门 WGS84 坐标 (BD09 类似) -> GCJ02
        [$gcjLng, $gcjLat] = LBSHelper::BD09ToGCJ02(116.403875, 39.915168);
        $this->assertIsFloat($gcjLng);
        $this->assertIsFloat($gcjLat);
    }

    public function testGCJ02ToBD09()
    {
        [$bdLng, $bdLat] = LBSHelper::GCJ02ToBD09(116.403875, 39.915168);
        $this->assertIsFloat($bdLng);
        $this->assertIsFloat($bdLat);
    }

    public function testGetMongoGeometry()
    {
        $geo = LBSHelper::getMongoGeometry(116.0, 39.0);
        $this->assertSame('Point', $geo['type']);
        $this->assertSame([116.0, 39.0], $geo['coordinates']);
    }

    public function testGetMongo2d()
    {
        $geo = LBSHelper::getMongo2d(116.0, 39.0);
        $this->assertSame([116.0, 39.0], $geo);
    }

    public function testGetCenterFromDegrees()
    {
        // 接收索引数组 [[lng, lat], [lng, lat]]
        $points = [
            [116.0, 39.0],
            [117.0, 40.0],
        ];
        $center = LBSHelper::getCenterFromDegrees($points);
        $this->assertIsArray($center);
        $this->assertCount(2, $center);
        $this->assertArrayHasKey(0, $center);
        $this->assertArrayHasKey(1, $center);
    }

    public function testGetAMAPRectangle()
    {
        // 参数是矩形字符串：左下;右上，返回包含四个角的数组
        $rect = LBSHelper::getAMAPRectangle('116.0119343,39.66127144;116.7829835,40.2164962');
        $this->assertIsArray($rect);
        $this->assertCount(4, $rect);
    }
}
