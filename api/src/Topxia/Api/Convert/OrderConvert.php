<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:22:04
 * @version $Id$
 */

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class OrderConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $order = ServiceKernel::instance()->createService('Order.OrderService')->getorder($id);
        if (empty($order)) {
            throw new \Exception('user not found');
        }
        return $order;
    }

}
