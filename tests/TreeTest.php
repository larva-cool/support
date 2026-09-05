<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Tests;

use InvalidArgumentException;
use Larva\Support\Tree;

class TreeTest extends TestCase
{
    /**
     * 基础数据：一级、二级、三级栏目
     * @return array
     */
    private function sampleData(): array
    {
        return [
            1 => ['id' => '1', 'parent_id' => 0, 'title' => '一级栏目一', 'sort' => 2],
            2 => ['id' => '2', 'parent_id' => 0, 'title' => '一级栏目二', 'sort' => 1],
            3 => ['id' => '3', 'parent_id' => 1, 'title' => '二级栏目一', 'sort' => 2],
            4 => ['id' => '4', 'parent_id' => 1, 'title' => '二级栏目二', 'sort' => 1],
            5 => ['id' => '5', 'parent_id' => 2, 'title' => '二级栏目三', 'sort' => 1],
            6 => ['id' => '6', 'parent_id' => 3, 'title' => '三级栏目一', 'sort' => 1],
            7 => ['id' => '7', 'parent_id' => 3, 'title' => '三级栏目二', 'sort' => 2],
        ];
    }

    public function testCreate(): void
    {
        $tree = Tree::create();
        $this->assertInstanceOf(Tree::class, $tree);
        $this->assertSame([], $tree->data);
    }

    public function testWithData(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create()->withData($data);
        $this->assertSame($data, $tree->data);
        $this->assertInstanceOf(Tree::class, $tree);
    }

    public function testWithConfigValid(): void
    {
        $tree = Tree::create()->withConfig('idKey', 'cat_id');
        $this->assertSame('cat_id', $tree->idKey);
    }

    public function testWithConfigInvalidThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Tree::create()->withConfig('notExistKey', 'value');
    }

    public function testWithOrder(): void
    {
        $tree = Tree::create()->withOrder('sort', 'desc');
        $this->assertSame('sort', $tree->orderKey);
        $this->assertSame('desc', $tree->orderDirection);

        $tree = Tree::create()->withOrder('sort', 'ASC');
        $this->assertSame('asc', $tree->orderDirection);
    }

    public function testBuildArray(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create()->withData($data)->buildArray(0);

        $this->assertIsArray($tree);
        $this->assertCount(2, $tree);

        // 第一个根节点应该有 spacer 和 child
        $first = $tree[0];
        $this->assertArrayHasKey('spacer', $first);
        $this->assertArrayHasKey('child', $first);
        $this->assertSame('└', $first['spacer']);

        // 第二个根节点（中间节点）应该是 ├
        $second = $tree[1];
        $this->assertSame('├', $second['spacer']);
    }

    public function testBuildArraySpacerHierarchy(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create()->withData($data)->buildArray(0);

        // 二级栏目应有前缀（取决于父节点的位置）
        $level1 = $tree[1]; // 末级 "一级栏目二"
        $this->assertSame('└', $level1['spacer']);

        // "一级栏目一" 的子节点（三级栏目）
        $level2 = $level1['child'] ?? [];
        $this->assertEmpty($level2);

        $root0 = $tree[0]; // "一级栏目一"
        $this->assertCount(2, $root0['child']);

        $l2First = $root0['child'][0];
        $l2Last = $root0['child'][1];

        // 因为 root0 是第一个根节点（中间），其子节点前缀应是 "│" + "&nbsp;"
        $this->assertStringContainsString('│', $l2First['spacer']);
        $this->assertStringContainsString('│', $l2Last['spacer']);

        // 末位二级节点应使用 └
        $this->assertStringContainsString('└', $l2Last['spacer']);
    }

    public function testBuildArrayWithSort(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create()
            ->withData($data)
            ->withOrder('sort', 'asc')
            ->buildArray(0);

        // asc 排序：sort=1 的 "一级栏目二"(id=2) 应排在前面
        $this->assertSame('2', $tree[0]['id']);
        $this->assertSame('1', $tree[1]['id']);
    }

    public function testGetListChild(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create();

        $child = $tree->getListChild($data, 1);
        $this->assertCount(2, $child);
        $this->assertSame('3', $child[3]['id']);
        $this->assertSame('4', $child[4]['id']);

        // 不存在的父 ID
        $this->assertSame([], $tree->getListChild($data, 999));
        // 空列表
        $this->assertSame([], $tree->getListChild([], 0));
    }

    public function testGetListChildSkipsInvalidRows(): void
    {
        $data = [
            'a' => ['id' => '1', 'parent_id' => 0, 'title' => 'A'],
            'b' => ['id' => '2'],   // 缺 parent_id
            'c' => ['parent_id' => 0, 'title' => 'C'], // 缺 id
        ];

        $tree = Tree::create();
        $child = $tree->getListChild($data, 0);
        $this->assertCount(1, $child);
        $this->assertSame('A', $child['a']['title']);
    }

    public function testGetListSelf(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create();

        $self = $tree->getListSelf($data, '3');
        $this->assertSame('二级栏目一', $self['title']);

        $this->assertSame([], $tree->getListSelf($data, '999'));
        $this->assertSame([], $tree->getListSelf([], '1'));
    }

    public function testGetListParents(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create();

        // id=6 的所有父节点
        // desc（默认）：子→父，应为 [3, 1]
        $parents = $tree->getListParents($data, '6', 'desc');
        $ids = array_column($parents, 'id');
        $this->assertSame(['3', '1'], $ids);

        // asc：父→子，应为 [1, 3]
        $parents = $tree->getListParents($data, '6', 'asc');
        $ids = array_column($parents, 'id');
        $this->assertSame(['1', '3'], $ids);

        // 根节点没有父节点
        $this->assertSame([], $tree->getListParents($data, '1'));
        $this->assertSame([], $tree->getListParents([], '1'));
    }

    public function testGetListParentsId(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create();

        $this->assertSame(['3', '1'], $tree->getListParentsId($data, '6', 'desc'));
        $this->assertSame([], $tree->getListParentsId($data, '999'));
        $this->assertSame([], $tree->getListParentsId([], '1'));
    }

    public function testGetListChildren(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create();

        // id=1 的所有后代
        $children = $tree->getListChildren($data, '1');
        $ids = array_column($children, 'id');
        // 顺序受 sort 影响，desc 时递归反序：应为 [6,7,3,4]（子→父）
        $this->assertContains('3', $ids);
        $this->assertContains('4', $ids);
        $this->assertContains('6', $ids);
        $this->assertContains('7', $ids);
        $this->assertCount(4, $children);

        // 空列表
        $this->assertSame([], $tree->getListChildren([], '1'));
        // 无子节点
        $this->assertSame([], $tree->getListChildren($data, '999'));
    }

    public function testGetListChildrenIds(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create();

        $ids = $tree->getListChildrenIds($data, '1');
        $this->assertCount(4, $ids);
        $this->assertContains('3', $ids);
        $this->assertContains('7', $ids);

        $this->assertSame([], $tree->getListChildrenIds($data, '999'));
    }

    public function testBuildFormatList(): void
    {
        $data = $this->sampleData();
        $tree = Tree::create()->withData($data)->buildArray(0);
        $flat = $tree->buildFormatList($tree);

        $this->assertCount(7, $flat);
        foreach ($flat as $row) {
            $this->assertArrayHasKey('spacer', $row);
            $this->assertArrayHasKey('has_child', $row);
            $this->assertArrayNotHasKey('child', $row);
        }

        // 根节点应有子节点
        $hasChildMap = array_column($flat, 'has_child', 'id');
        $this->assertSame(1, $hasChildMap['1']);
        $this->assertSame(1, $hasChildMap['3']);
        // 叶子节点
        $this->assertSame(0, $hasChildMap['4']);
        $this->assertSame(0, $hasChildMap['7']);

        // 空输入
        $this->assertSame([], $tree->buildFormatList([]));
    }

    public function testEmptyData(): void
    {
        $tree = Tree::create();
        $this->assertSame([], $tree->buildArray(0));
        $this->assertSame([], $tree->buildFormatList());
    }

    public function testNonExistentRootId(): void
    {
        $tree = Tree::create()->withData($this->sampleData());
        $this->assertSame([], $tree->buildArray(999));
    }

    public function testIntegerIdKeys(): void
    {
        $data = [
            ['id' => 1, 'parent_id' => 0, 'title' => 'A'],
            ['id' => 2, 'parent_id' => 1, 'title' => 'B'],
            ['id' => 3, 'parent_id' => 1, 'title' => 'C'],
        ];
        $tree = Tree::create()->withData($data);
        $child = $tree->getListChild($data, 1);
        $this->assertCount(2, $child);

        $built = $tree->buildArray(0);
        $this->assertCount(1, $built);
        $this->assertSame('A', $built[0]['title']);
        $this->assertCount(2, $built[0]['child']);
    }
}
