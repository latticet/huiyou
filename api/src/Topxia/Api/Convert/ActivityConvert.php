<?php

namespace Topxia\Api\Convert;
use Topxia\Service\Common\ServiceKernel;

class ActivityConvert implements Convert
{
    //根据id等参数获取完整数据
    public function convert($id)
    {
        $activity = ServiceKernel::instance()->createService('Article.ArticleService')->getArticle($id);
        if (empty($activity)) {
            throw new \Exception('user not found');
        }
        return $activity;
    }

}

