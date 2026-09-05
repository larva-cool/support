<?php

/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare(strict_types=1);

namespace Larva\Support;

use InvalidArgumentException;

/**
 * 树
 *
 * 用于将扁平数组（带 parent_id 关系）转换为树形结构，
 * 或生成带连接符（├、└、│）的可视化层级数组。
 */
class Tree
{
    /**
     * 生成树型结构所需要的2维数组
     * @var array
     */
    public array $data = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     *
     *  - [0] 中间层节点的连接符
     *  - [1] 非末级节点的连接符
     *  - [2] 末级节点的连接符
     * @var array
     */
    public array $icon = ['│', '├', '└'];

    /**
     * 占位空格（用于 HTML 输出）
     * @var string
     */
    public string $blankSpace = '&nbsp;';

    // 查询
    public string $idKey = 'id';

    public string $parentIdKey = 'parent_id';

    public string $spacerKey = 'spacer';

    public string $hasChildKey = 'has_child';

    // 返回子级key
    public string $buildChildKey = 'child';

    /**
     * 排序字段；为空时保持原顺序
     * @var string
     */
    public string $orderKey = '';

    /**
     * 排序方向：asc | desc
     * @var string
     */
    public string $orderDirection = 'asc';

    /**
     * 创建
     * @return Tree
     */
    public static function create(): Tree
    {
        return new static();
    }

    /**
     * 构造函数，初始化类
     * @param array $data 2维数组，例如：
     * array(
     *      1 => array('id'=>'1','parent_id'=>0,'title'=>'一级栏目一'),
     *      2 => array('id'=>'2','parent_id'=>0,'title'=>'一级栏目二'),
     *      3 => array('id'=>'3','parent_id'=>1,'title'=>'二级栏目一'),
     *      4 => array('id'=>'4','parent_id'=>1,'title'=>'二级栏目二'),
     *      5 => array('id'=>'5','parent_id'=>2,'title'=>'二级栏目三'),
     *      6 => array('id'=>'6','parent_id'=>3,'title'=>'三级栏目一'),
     *      7 => array('id'=>'7','parent_id'=>3,'title'=>'三级栏目二')
     * )
     * @return $this
     */
    public function withData(array $data = []): Tree
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置配置项（仅允许设置当前对象已声明的属性）
     * @param string $key  属性名
     * @param mixed  $value 值
     * @return $this
     * @throws InvalidArgumentException 当属性不存在时
     */
    public function withConfig(string $key, mixed $value): Tree
    {
        if (!property_exists($this, $key)) {
            throw new InvalidArgumentException(sprintf('Tree 配置项 [%s] 不存在', $key));
        }
        $this->{$key} = $value;
        return $this;
    }

    /**
     * 设置同级排序字段
     * @param string $key        用于排序的字段名
     * @param string $direction  asc | desc
     * @return $this
     */
    public function withOrder(string $key, string $direction = 'asc'): Tree
    {
        $this->orderKey = $key;
        $this->orderDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        return $this;
    }

    /**
     * 构建可视化的树型数组
     *
     * 每个节点会带上 $spacerKey（连接符前缀）、$hasChildKey（是否有子节点，0/1）以及
     * $buildChildKey（子节点数组）。
     *
     * @param string|int $id           要查询的父 ID（一般传 0 表示根节点）
     * @param string     $itemPrefix   当前层级的前缀（递归内部使用，外部调用通常传空）
     * @return array
     */
    public function buildArray(string|int $id, string $itemPrefix = ''): array
    {
        $child = $this->getListChild($this->data, $id);
        if (!is_array($child)) {
            return [];
        }

        $child = $this->sortList($child);
        $data = [];
        $number = 1;
        $total = count($child);

        foreach ($child as $value) {
            $childInfo = $value;
            $j = '';
            if ($number == $total) {
                if (isset($this->icon[2])) {
                    $j .= $this->icon[2];
                }
                $k = $itemPrefix !== '' ? $this->blankSpace : '';
            } else {
                if (isset($this->icon[1])) {
                    $j .= $this->icon[1];
                }
                $k = $itemPrefix !== '' ? ($this->icon[0] ?? '') . $this->blankSpace : '';
            }
            $spacer = $itemPrefix !== '' ? $itemPrefix . $j : $j;
            $childInfo[$this->spacerKey] = $spacer;

            $childList = $this->buildArray($value[$this->idKey], $itemPrefix . $k);
            if (!empty($childList)) {
                $childInfo[$this->buildChildKey] = $childList;
            }

            $data[] = $childInfo;
            $number++;
        }

        return $data;
    }

    /**
     * 所有父节点
     *
     * @param array         $list      数据集
     * @param string|int    $parent_id 节点的 parent_id
     * @param string        $sort      asc：父→子；desc：子→父
     * @return array
     */
    public function getListParents(array $list = [], string|int $parent_id = '', string $sort = 'desc'): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $value) {
            if ((string)$value[$this->idKey] === (string)$parent_id) {
                $result[] = $value;
                $parent = $this->getListParents($list, $value[$this->parentIdKey], $sort);
                if (!empty($parent)) {
                    if ($sort === 'asc') {
                        $result = array_merge($result, $parent);
                    } else {
                        $result = array_merge($parent, $result);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 所有父节点的 ID 列表
     * @param array      $list      数据集
     * @param string|int $parent_id 节点的 parent_id
     * @return array
     */
    public function getListParentsId(array $list = [], string|int $parent_id = ''): array
    {
        $parents = $this->getListParents($list, $parent_id);
        if (empty($parents)) {
            return [];
        }

        $ids = [];
        foreach ($parents as $parent) {
            $ids[] = $parent[$this->idKey];
        }

        return $ids;
    }

    /**
     * 获取当前 ID 的所有子节点（递归）
     * @param array      $list 数据集
     * @param string|int $id   当前 id
     * @param string     $sort asc：父→子；desc：子→父
     * @return array
     */
    public function getListChildren(array $list = [], string|int $id = '', string $sort = 'desc'): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $result = [];
        foreach ($list as $value) {
            if ((string)$value[$this->parentIdKey] === (string)$id) {
                $result[] = $value;
                $child = $this->getListChildren($list, $value[$this->idKey], $sort);
                if (!empty($child)) {
                    if ($sort === 'asc') {
                        $result = array_merge($result, $child);
                    } else {
                        $result = array_merge($child, $result);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取当前 ID 的所有子节点 ID（递归）
     * @param array      $list 数据集
     * @param string|int $id   当前 id
     * @return array
     */
    public function getListChildrenIds(array $list = [], string|int $id = ''): array
    {
        $childrenIds = $this->getListChildren($list, $id);
        if (empty($childrenIds)) {
            return [];
        }
        $ids = [];
        foreach ($childrenIds as $child) {
            $ids[] = $child[$this->idKey];
        }
        return $ids;
    }

    /**
     * 得到子级第一级数组（仅一层）
     * @param array      $list 数据集
     * @param string|int $id   当前 id
     * @return array
     */
    public function getListChild(array $list, string|int $id): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $id = (string)$id;
        $newData = [];
        foreach ($list as $key => $data) {
            if (!isset($data[$this->parentIdKey], $data[$this->idKey])) {
                continue;
            }
            $dataParentId = (string)$data[$this->parentIdKey];
            if ($dataParentId === $id) {
                $newData[$key] = $data;
            }
        }

        return $newData;
    }

    /**
     * 获取 ID 自己的数据
     * @param array      $list 数据集
     * @param string|int $id   当前 id
     * @return array 空数组表示未找到
     */
    public function getListSelf(array $list, string|int $id): array
    {
        if (empty($list) || !is_array($list)) {
            return [];
        }

        $id = (string)$id;
        foreach ($list as $data) {
            if (!isset($data[$this->idKey])) {
                continue;
            }
            $dataId = (string)$data[$this->idKey];
            if ($dataId === $id) {
                return $data;
            }
        }
        return [];
    }

    /**
     * 将 buildArray 的结果返回为二维数组（铺平）
     * @param array $data 由 buildArray 生成的结构
     * @return array
     */
    public function buildFormatList(array $data = []): array
    {
        if (empty($data)) {
            return [];
        }
        $list = [];
        foreach ($data as $v) {
            if (!is_array($v) || empty($v)) {
                continue;
            }
            if (!isset($v[$this->spacerKey])) {
                $v[$this->spacerKey] = '';
            }
            $child = $v[$this->buildChildKey] ?? [];
            $v[$this->hasChildKey] = !empty($child) ? 1 : 0;
            unset($v[$this->buildChildKey]);
            $list[] = $v;
            if (!empty($child)) {
                $list = array_merge($list, $this->buildFormatList($child));
            }
        }
        return $list;
    }

    /**
     * 根据 $orderKey / $orderDirection 对列表进行稳定排序
     * @param array $list 待排序数据
     * @return array 排序后的数据（保留原始键名）
     */
    protected function sortList(array $list): array
    {
        if ($this->orderKey === '' || empty($list)) {
            return $list;
        }

        $key = $this->orderKey;
        $direction = $this->orderDirection;
        uasort($list, static function ($a, $b) use ($key, $direction): int {
            $av = $a[$key] ?? null;
            $bv = $b[$key] ?? null;
            if ($av == $bv) {
                return 0;
            }
            $cmp = ($av < $bv) ? -1 : 1;
            return $direction === 'desc' ? -$cmp : $cmp;
        });
        return $list;
    }
}
