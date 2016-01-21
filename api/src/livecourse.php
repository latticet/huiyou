<?php
/**
 * @zhibo
 * @author zhangyaowen
 * @date 20151216
 */
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
$api = $app['controllers_factory'];
//首页获取四条正在直播
$api->get('/{id}', function ($id) {
    $course = convert($id, 'course');
    
    return filter($course, 'course');
});
//收藏课程
/*
** 参数 **

| 名称  | 类型  | 必需   | 说明 |
| ---- | ----- | ----- | ---- |
| method | string | 否 | 值为delete,表明当前为delete方法 |

** 响应 **

```
{
    "xxx": "xxx"
}
```
*/
$api->post('/{id}/favorite', function (Request $request, $id) {
    $course = convert($id, 'course');
    $method = $request->request->get('method', 'post');
    if ($method == 'delete') {
        $result = ServiceKernel::instance()->createService('Course.CourseService')->unFavoriteCourse($course['id']);
    } else {
        $result = ServiceKernel::instance()->createService('Course.CourseService')->favoriteCourse($course['id']);
    }
    
    return array(
        'success' => $result
    );
});
//获取分类代码
$api->get('/category/codes', function () {
    $categoryCodes = ServiceKernel::instance()->createService('Taxonomy.CategoryService')->findAllCategories();
    
    return $categoryCodes;
});
//根据分类编码获取课程
$api->get('/category/{code}', function (Request $request, $code) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $is_live = $request->query->get('is_live', 0);
    $sortField = $request->query->get('sortField', 'id');
    $sort = $request->query->get('sort', 'DESC');
    $orderBy = array();
    $orderBy[] = $sortField;
    $orderBy[] = $sort;
    $conditions = array();
    if ($code == 'all') {
    } else {
        $categoryArray = ServiceKernel::instance()->createService('Taxonomy.CategoryService')->getCategoryByCode($code);
        $childrenIds = ServiceKernel::instance()->createService('Taxonomy.CategoryService')->findCategoryChildrenIds($categoryArray['id']);
        $categoryIds = array_merge($childrenIds, array(
            $categoryArray['id']
        ));
        $conditions['categoryIds'] = $categoryIds;
    }
    // $conditions['parentId'] = 0;
    $conditions['status'] = 'published';
    if ($is_live == 1) {
        $conditions['type'] = 'live';
    } elseif ($is_live == 2) {
        //什么都不做
        
    } else {
        $conditions['type'] = 'normal';
    }
    $courses = ServiceKernel::instance()->createService('Course.CourseService')->searchCourses($conditions, $orderBy, $start, $limit);
    $count = ServiceKernel::instance()->createService('Course.CourseService')->searchCourseCount($conditions);
    $data = array();
    $data['course_list'] = $courses;
    $data['count'] = $count;
    
    return $data;
});
//根据id获取一个课程Lesson信息
$api->get('/{courseId}/lesson/{lessonId}', function ($courseId, $lessonId) {
    $lesson = ServiceKernel::instance()->createService('Course.CourseService')->getCourseLesson($courseId, $lessonId);
    
    return $lesson;
});
//获取评论
$api->get('/{courseId}/reviews', function (Request $request, $courseId) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $reviewService = ServiceKernel::instance()->createService('Course.ReviewService');
    $reviews = $reviewService->findCourseReviews($courseId, $start, $limit);
    $count = $reviewService->getCourseReviewCount($courseId);
    $data = array();
    $data['data_list'] = filters($reviews, 'course');
    $data['count'] = $count;
    
    return $data;
});
//获取个人评论
$api->get('/{courseId}/reviews/user/{userId}', function ($courseId, $userId) {
    $reviewService = ServiceKernel::instance()->createService('Course.ReviewService');
    $review = $reviewService->getUserCourseReview($userId, $courseId);
    $review = filter($review, 'course');
    
    return $review;
});
//添加个人评论
$api->post('/{courseId}/reviews/user/{userId}', function (Request $request, $courseId, $userId) {
    $fields = array();
    $fields['rating'] = $request->request->get('rating');
    $fields['content'] = $request->request->get('content');
    $fields['userId'] = $userId;
    $fields['courseId'] = $courseId;
    $reviewService = ServiceKernel::instance()->createService('Course.ReviewService');
    $review = $reviewService->saveReview($fields);
    $review = filter($review, 'course');
    
    return $review;
});
//13关键词搜索
$api->get('/search/', function (Request $request) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $courses = $paginator = null;
    $keywords = $request->query->get('q');
    $keywords = trim($keywords);
    $conditions = array();
    $conditions['status'] = 'published';
    $conditions['title'] = $keywords;
    $courseService = ServiceKernel::instance()->createService('Course.CourseService');
    $count = $courseService->searchCourseCount($conditions);
    $courses = $courseService->searchCourses($conditions, 'latest', $start, $limit);
    $data = array();
    $data['courses'] = $courses;
    $data['count'] = $count;
    
    return $data;
});
//14获取首页轮播
$api->get('/blocks/', function (Request $request) {
    $settingService = ServiceKernel::instance()->createService('System.SettingService');
    $name = 'theme';
    $theme = $settingService->get($name);
    $blockService = ServiceKernel::instance()->createService('Content.BlockService');
    $code = "{$theme['uri']}:home_top_banner";
    $blocks = $blockService->getBlockByCode($code);    
    $data=array();
    $data['data']=$blocks['data'];    
    return $data;
});

return $api;
