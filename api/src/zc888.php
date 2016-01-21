<?php

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

$api = $app['controllers_factory'];

//根据id获取一个课程信息
$api->get('/{id}', function ($id) {
    $zc888 = convert($id,'zc888');
    
    return filter($zc888, 'zc888');
});

return $api;