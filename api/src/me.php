<?php
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\Common\FileToolkit;
use Symfony\Component\HttpFoundation\File\File;
$api = $app['controllers_factory'];
//获取当前用户信息
/*
** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/', function (Request $request) {
    $user = getCurrentUser();
    $user = is_array($user) ? $user : $user->toArray();
    
    return filter($user, 'me');
});
//修改当前用户信息
$api->post('/profile/post', function (Request $request) {
    $user = getCurrentUser();
    $UserService = ServiceKernel::instance()->createService('User.UserService');
    $userId = $request->request->get('userId', $user['id']);
    $profile = $request->request->all();
    $AuthService = ServiceKernel::instance()->createService('User.AuthService');
    $nickname = $profile['nickname'];
    $nickname = $AuthService->changeNickname($userId, $nickname);
    $profile = $UserService->updateUserProfile($userId, $profile);
    $user = $UserService->getUser($userId);
    $profile['nickname'] = $user['nickname'];
    
    return $profile;
});
//获取当前用户课程
/*
[支持分页](global-parameter.md)

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 否 | 课程类型 |
| relation | string | 否 | 用户相对于课程状态 |


`type`的值有：

  * normal : 普通课程
  * live : 直播课程

`relation`的值有：

  * learning : 在学
  * learned : 已学完
  * teaching : 在教
  * favorited : 收藏

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/courses', function (Request $request) {
    $conditions = $request->query->all();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $type = $request->query->get('type', '');
    $relation = $request->query->get('relation', '');
    $user = getCurrentUser();
    if ($relation == 'learning') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourseCount($user['id'], $conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourses($user['id'], $start, $limit, empty($type) ? array() : array(
            'type' => $type
        ));
    } elseif ($relation == 'learned') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeanedCourseCount($user['id'], $conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeanedCourses($user['id'], $start, $limit, empty($type) ? array() : array(
            'type' => $type
        ));
    } elseif ($relation == 'teaching') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserTeachCourseCount(array(
            'userId' => $user['id']
        ) , false);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserTeachCourses(array(
            'userId' => $user['id']
        ) , $start, $limit, false);
    } else if ($relation == 'favorited') {
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserFavoritedCourseCount($user['id']);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserFavoritedCourses($user['id'], $start, $limit);
    } else {
        //全部
        // $courses = array();
        // $total = 0;
        $total = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourseCount($user['id'], $conditions);
        $courses = ServiceKernel::instance()->createService('Course.CourseService')->findUserLeaningCourses($user['id'], $start, $limit, empty($type) ? array() : array(
            'type' => $type
        ));
    }
    
    return array(
        'data' => $courses,
        'total' => $total
    );
});
//获得当前用户的关注者
/*

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/followers', function (Request $request) {
    $user = getCurrentUser();
    $follwers = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollower($user['id']);
    
    return $follwers;
});
//获得当前用户关注的人
/*

** 响应 **

```
{
    "xxx": "xxx"
}
```

*/
$api->get('/followings', function (Request $request) {
    $user = getCurrentUser();
    $follwings = ServiceKernel::instance()->createService('User.UserService')->findAllUserFollowing($user['id']);
    
    return $follwings;
});
//获得当前用户虚拟币账户信息
$api->get('/accounts', function () {
    $user = getCurrentUser();
    $accounts = ServiceKernel::instance()->createService('Cash.CashAccountService')->getAccountByUserId($user['id']);
    if (empty($accounts)) {
        throw new \Exception('accounts not found');
    }
    
    return $accounts;
});
/*
## 获取当前用户的话题
    GET /me/coursethreads

[支持分页](global-parameter.md)

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 否 | 类型,未传则取全部类型 |

`type`的值有：

  * question : 问答
  * discussion : 话题

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->get('/coursethreads', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $type = $request->query->get('type', '');
    $conditions = empty($type) ? array() : array(
        'type' => $type
    );
    $conditions['userId'] = $user['id'];
    $total = ServiceKernel::instance()->createService('Course.ThreadService')->searchThreadCount($conditions);
    $coursethreads = ServiceKernel::instance()->createService('Course.ThreadService')->searchThreads($conditions, 'created', $start, $limit);
    
    return array(
        'data' => $coursethreads,
        'total' => $total
    );
});
/*
## 获取当前用户黑名单
    GET /me/blacklists

** 响应 **

```
[
    {
        id:{blacklist-id},
        userId:{blacklist-userId},
        ...
    },
    {
        id:{blacklist-id},
        userId:{blacklist-userId},
        ...
    },
    ...
]
```

*/
$api->get('/blacklists', function () {
    $user = getCurrentUser();
    $blacklists = ServiceKernel::instance()->createService('User.BlacklistService')->findBlacklistsByUserId($user['id']);
    
    return filters($blacklists, 'blacklist');
});
/*
## 获取当前用户互粉用户
    GET /me/friends

[支持分页](global-parameter.md)

** 响应 **

```
{
    "data": "{friend-list}"
    "total": "{totalCount}"
}
```

*/
$api->get('/friends', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $friends = ServiceKernel::instance()->createService('User.UserService')->findFriends($user['id'], $start, $limit);
    $count = ServiceKernel::instance()->createService('User.UserService')->findFriendCount($user['id']);
    
    return array(
        'data' => filters($friends, 'user') ,
        'total' => $count
    );
});
/*
## 获取当前用户通知
    GET /me/notifications

[支持分页](global-parameter.md)

** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| type | string | 否 | 类型,未传则取全部类型 |

`type`的值有：

  * user-follow : 关注好友

** 响应 **

```
{
    "data": "{friend-list}"
    "total": "{totalCount}"
}
```

*/
$api->get('/notifications', function (Request $request) {
    $user = getCurrentUser();
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $type = $request->query->get('type', '');
    $userId = $request->query->get('userId', $user['id']);
    $conditions['userId'] = $userId;
    if (!empty($type)) {
        $conditions['type'] = $type;
    }
    $notifications = ServiceKernel::instance()->createService('User.NotificationService')->searchNotifications($conditions, array(
        'createdTime',
        'DESC'
    ) , $start, $limit);
    $count = ServiceKernel::instance()->createService('User.NotificationService')->searchNotificationCount($conditions);
    
    return array(
        'data' => filters($notifications, 'notification') ,
        'total' => $count
    );
});
//修改密码
$api->post('/change_password', function (Request $request) {
    $user = getCurrentUser();
    $method = $request->request->get('method');
    $current_password = $request->request->get('current_password');
    $new_password = $request->request->get('new_password');
    $user_id = $user['id'];
    //$user['currentIp']=$current_ip;
    $status = ServiceKernel::instance()->createService('User.UserService')->verifyPassword($user_id, $current_password);
    if ($status) {
        $result = ServiceKernel::instance()->createService('User.UserService')->changePassword($user_id, $new_password);
    } else {
        $result = false;
    }
    
    return array(
        'success' => $result
    );
});
//上传头像
$api->post('/avatar/post', function (Request $request) {
    $user = getCurrentUser();
    $userId = $user['id'];
    //上传
    $groupCode = 'user';
    $file = $request->files->get('file');
    if (!FileToolkit::isImageFile($file)) {
        throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
    }
    $FileService = ServiceKernel::instance()->createService('Content.FileService');
    $record = $FileService->uploadFile($groupCode, $file);
    $parsed = $FileService->parseFileUri($record['uri']);
    $imgInfo = getimagesize($parsed['fullpath']);
    $width = $imgInfo[0];
    $height = $imgInfo[1];
    $x = 0;
    $y = 0;
    $x2 = $width;
    $y2 = $height;
    $w = $width;
    $h = $height;
    //切图
    $options = array();
    $options['x'] = $request->request->get('x', $x);
    $options['y'] = $request->request->get('y', $y);
    $options['x2'] = $request->request->get('x2', $x2);
    $options['y2'] = $request->request->get('y2', $y2);
    $options['w'] = $request->request->get('w', $w);
    $options['h'] = $request->request->get('h', $h);
    $options['width'] = $width; //方形宽高
    $options['height'] = $height;
    $imgs = array();
    $large = array(
        // 200,
        // 200
        $width,
        $height
    );
    $imgs['large'] = $large;
    $medium = array(
        // 120,
        // 120
        $width,
        $height
    );
    $imgs['medium'] = $medium;
    $small = array(
        // 48,
        // 48
        $width,
        $height
    );
    $imgs['small'] = $small;
    $options['imgs'] = $imgs;
    $options['group'] = $groupCode;
    if (empty($options['group'])) {
        $options['group'] = "default";
    }
    $filePaths = FileToolKit::cropImages($parsed["fullpath"], $options);
    $fields = array();
    
    foreach ($filePaths as $key => $value) {
        $file = $FileService->uploadFile($options["group"], new File($value));
        $fields[] = array(
            "type" => $key,
            "id" => $file['id']
        );
    }
    if (isset($options["deleteOriginFile"]) && $options["deleteOriginFile"] == 0) {
        $fields[] = array(
            "type" => "origin",
            "id" => $record['id']
        );
    } else {
        $FileService->deleteFileByUri($record["uri"]);
    }
    //保存图片地址到个人信息
    $UserService = ServiceKernel::instance()->createService('User.UserService');
    $user = $UserService->changeAvatar($userId, $fields);
    if (isset($user['smallAvatar'])) {
        $user['smallAvatar'] = parseUrl($user['smallAvatar']);
    }
    if (isset($user['mediumAvatar'])) {
        $user['mediumAvatar'] = parseUrl($user['mediumAvatar']);
    }
    if (isset($user['largeAvatar'])) {
        $user['largeAvatar'] = parseUrl($user['largeAvatar']);
    }
    
    return $user;
});
//上传图片
$api->post('/picture/post', function (Request $request) {
    $groupCode = 'default';
    $file = $request->files->get('file');
    if (!FileToolkit::isImageFile($file)) {
        throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
    }
    $FileService = ServiceKernel::instance()->createService('Content.FileService');
    $record = $FileService->uploadFile($groupCode, $file);
    
    return $record;
});
//添加收藏
$api->get('/course/favorite', function (Request $request) {
    $CourseService = ServiceKernel::instance()->createService('Course.CourseService');
    $user = getCurrentUser();
    $userId = $request->query->get('userId', $user['id']);
    $courseId = $request->query->get('courseId', 0);
    $CourseService->favoriteCourseByUserId($courseId, $userId);
    
    return array(
        'success' => 1
    );
});
//取消收藏
$api->get('/course/unfavorite', function (Request $request) {
    $CourseService = ServiceKernel::instance()->createService('Course.CourseService');
    $user = getCurrentUser();
    $userId = $request->query->get('userId', $user['id']);
    $courseId = $request->query->get('courseId', 0);
    $CourseService->unfavoriteCourseByUserId($courseId, $userId);
    
    return array(
        'success' => 1
    );
});
//收藏列表
$api->get('/course/favoriteList', function (Request $request) {
    $CourseService = ServiceKernel::instance()->createService('Course.CourseService');
    $user = getCurrentUser();
    $userId = $request->query->get('userId', $user['id']);
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $courses = $CourseService->findUserFavoritedCourses($userId, $start, $limit);
    $data=array();
    $data['courses']=$courses;
    $data['count']=count($courses);
    return $data;
});

return $api;
