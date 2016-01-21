<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:48:21
 * @version $Id$
 */

namespace Topxia\Api\Filter;

class HomeworkFilter implements Filter
{
	//输出前的字段控制
    //查看权限,附带内容可以写在这里
    public function filter(array &$data)
    {
        $data['create_at'] = date('c', $data['create_at']);
        if(isset($data['pic'])){
            $data['pic']=$this->picParse($data['pic']);

        }
        

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
    protected function picParse($pic){
        $url='';
        if(!empty($pic)){
            $public_mark='public:/';
            $public_path='/files';
            $private_mark='private:/';
            $private_path='/app/data/private_files';
            if(strpos($pic, $public_mark)!==false){
                $url = str_replace($public_mark, $public_path, $pic);

            }elseif (strpos($pic, $private_mark)!==false) {
               $url = str_replace($private_mark, $private_path, $pic);
            }
        }
        return $url;
    }

}