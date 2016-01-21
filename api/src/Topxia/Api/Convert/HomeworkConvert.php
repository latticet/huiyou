<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:47:37
 * @version $Id$
 */

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class HomeworkConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $homework = ServiceKernel::instance()->createService('Homework:Homework.HomeworkService')->gethomework($id);
        if (empty($homework)) {
            throw new \Exception('user not found');
        }
        return $homework;
    }

}
