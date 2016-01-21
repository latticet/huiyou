<?php
/**
 *
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:20:47
 * @version $Id$
 */
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;
$api = $app['controllers_factory'];
//显示一条订单信息
$api->get('/show/{orderId}', function ($orderId) {
    $order = convert($orderId, 'order');
    
    return filter($order, 'order');
});
//创建一条订单信息
$api->post('/course/{targetId}/create', function (Request $request, $targetId) {
    $fields = $request->request->all();
    $user = getCurrentUser();
    $targetType = $fields["targetType"];
    
    // $maxRate = $fields["maxRate"];
    $totalPrice = $fields["totalPrice"];
    $amount = $fields["shouldPayMoney"];
    $cashRate = 1;
    $priceType = "RMB";
    $processor = OrderProcessorFactory::create($targetType);
    $orderFileds = array(
        'priceType' => $priceType,
        'totalPrice' => $totalPrice,
        'amount' => $amount,
        'coinRate' => $cashRate,
        'coinAmount' => empty($fields["coinPayAmount"]) ? 0 : $fields["coinPayAmount"],
        'userId' => $user["id"],
        'payment' => 'alipay',
        'targetId' => $targetId,
        'coupon' => empty($coupon) ? '' : $coupon,
        'couponDiscount' => empty($couponDiscount) ? 0 : $couponDiscount
    );
    $order = $processor->createOrder($orderFileds, $fields);
    
    return filter($order, 'order');
});
//显示一条订单信息
// $api->get('/detail', function ($id) {
//     $order = convert($id,'order');
//     return filter($order, 'order');
// });
//显示我的订单列表 全部 待支付 已支付 已取消
$api->get('/list', function (Request $request) {
    $user = getCurrentUser();
    $orders = array();
    $orderBy = array(
        'id',
        'DESC'
    );
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $status = $request->query->get('status', ''); //paid cancelled created
    $conditions = array();
    $conditions['userId'] = $user['id'];
    if (!empty($status)) {
        $conditions['status'] = $status;
    }
    $orders = ServiceKernel::instance()->createService('Order.OrderService')->searchOrders($conditions, $orderBy, $start, $limit);
    $count = ServiceKernel::instance()->createService('Order.OrderService')->searchOrderCount($conditions);
    $data = array();
    $data['order_list'] = $orders;
    $data['count'] = $count;
    
    return $data;
});
//取消一条订单信息
$api->get('/cancel/{orderId}', function ($orderId) {    
    $OrderService = ServiceKernel::instance()->createService('Order.OrderService');

    $order = $OrderService->cancelOrder($orderId);

    return filter($order, 'order');
});
//订单不允许删除
// $api->get('/detail', function ($id) {
//     $order = convert($id,'order');
//     return filter($order, 'order');
// });
//退款一条订单信息
// $api->get('/detail', function ($id) {
//     $order = convert($id,'order');
//     return filter($order, 'order');
// });
//支付一条订单信息
// $api->get('/detail', function ($id) {
//     $order = convert($id,'order');
//     return filter($order, 'order');
// });
return $api;
