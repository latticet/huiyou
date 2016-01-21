<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:42:38
 * @version $Id$
 */

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class PayConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $pay = ServiceKernel::instance()->createService('pay.payService')->getpay($id);
        if (empty($pay)) {
            throw new \Exception('user not found');
        }
        return $pay;
    }

}
