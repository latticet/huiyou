<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-09 10:45:50
 * @version $Id$
 */

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class ClassroomConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $Classroom = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->getClassroom($id);
        if (empty($Classroom)) {
            throw new \Exception('user not found');
        }
        return $Classroom;
    }

}
