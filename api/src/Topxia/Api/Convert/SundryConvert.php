<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class SundryConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $sundry = ServiceKernel::instance()->createService('sundry.sundryService')->getSundry($id);
        if (empty($sundry)) {
            throw new \Exception('user not found');
        }
        return $sundry;
    }

}

