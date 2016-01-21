<?php
namespace Topxia\Service\Zc888\Impl;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Common\SimpleValidator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Zc888\Zc888Service;
use Topxia\Service\Zc888\CurrentZc888;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class Zc888ServiceImpl extends BaseService implements Zc888Service
{
    function getZc888(){
        $zc888=array();
        $zc888['id']=003550;
        $zc888['name']='赵昌';
        $zc888['age']=18;
        $zc888['createdTime']=time();
return $zc888;
    }

}

class Zc888Serialize
{
}