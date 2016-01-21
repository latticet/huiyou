<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:24:29
 * @version $Id$
 */

namespace Topxia\Api\Filter;

class OrderFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        $data['createdTime'] = date('c', $data['createdTime']);

        return $data;
    }

    public function filters(array &$datas)
    {
        $num = 0;
        $results = array();
        foreach ($datas as $data) {
            $results[$num] = $this->filter($data);
            $num++;
        }
        return $results;
    }

}

