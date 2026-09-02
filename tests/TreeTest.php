<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use Larva\Support\Tree;

class TreeTest extends TestCase
{
    public function testBuildArray()
    {
        $data = array(
            1 => array('id' => '1', 'parent_id' => 0, 'title' => '一级栏目一'),
            2 => array('id' => '2', 'parent_id' => 0, 'title' => '一级栏目二'),
            3 => array('id' => '3', 'parent_id' => 1, 'title' => '二级栏目一'),
            4 => array('id' => '4', 'parent_id' => 1, 'title' => '二级栏目二'),
            5 => array('id' => '5', 'parent_id' => 2, 'title' => '二级栏目三'),
            6 => array('id' => '6', 'parent_id' => 3, 'title' => '三级栏目一'),
            7 => array('id' => '7', 'parent_id' => 3, 'title' => '三级栏目二')
        );
        $tree = Tree::create()->withData($data)->buildArray(0);
        $this->assertIsArray($tree);
    }
}
