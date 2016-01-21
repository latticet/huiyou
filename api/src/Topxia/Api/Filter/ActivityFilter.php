<?php
namespace Topxia\Api\Filter;

class ActivityFilter implements Filter {
    //输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array & $data) {
        $data['createdTime'] = date('c', $data['createdTime']);
        if (isset($data['thumb']) && !empty($data['thumb'])) {
            $data['thumb'] = parseUrl($data['thumb']);
            $data['originalThumb'] = parseUrl($data['originalThumb']);
        }
        if (isset($data['detail_thumb']) && !empty($data['detail_thumb'])) {
            $data['detail_thumb'] = parseUrl($data['detail_thumb']);
            $data['detail_originalThumb'] = parseUrl($data['detail_originalThumb']);
        }
        if (isset($data['carousel01_thumb']) && !empty($data['carousel01_thumb'])) {
            $data['carousel01_thumb'] = parseUrl($data['carousel01_thumb']);
            $data['carousel01_originalThumb'] = parseUrl($data['carousel01_originalThumb']);
        }
        if (isset($data['carousel02_thumb']) && !empty($data['carousel02_thumb'])) {
            $data['carousel02_thumb'] = parseUrl($data['carousel02_thumb']);
            $data['carousel02_originalThumb'] = parseUrl($data['carousel02_originalThumb']);
        }
        if (isset($data['carousel03_thumb']) && !empty($data['carousel02_thumb'])) {
            $data['carousel03_thumb'] = parseUrl($data['carousel03_thumb']);
            $data['carousel03_originalThumb'] = parseUrl($data['carousel03_originalThumb']);
        }
        
        return $data;
    }
    public function filters(array & $datas) {
        $num = 0;
        $results = array();
        
        foreach ($datas as $data) {
            $results[$num] = $this->filter($data);
            $num++;
        }
        
        return $results;
    }
}
