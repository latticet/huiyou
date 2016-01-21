<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class Zc888Convert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $zc888 = ServiceKernel::instance()->createService('Zc888.Zc888Service')->getZc888($id);
        if (empty($zc888)) {
            throw new \Exception('user not found');
        }
        return $zc888;
    }

}

