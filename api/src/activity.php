<?php
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\Common\ArrayToolkit;
$api = $app['controllers_factory'];

//线上id为5，线下id为6

//获取一个活动(文章)及所有评论
$api->get('/{id}', function (Request $request, $id) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $orderBy = array(
        'id',
        'DESC'
    );
    $conditions = array();
    $conditions['targetId'] = $id;
    $conditions['threadId'] = 0;
    $activity = convert($id, 'activity');
    //添加报名人数
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $temp_count = $ArticleService->getArticleLikesCountByArticleId($activity['id']);
    $activity['enroll'] = $temp_count;
    $threadService = ServiceKernel::instance()->createService('Thread.ThreadService');
    $threads = $threadService->searchPosts($conditions, $orderBy, $start, $limit);
    $UserService = ServiceKernel::instance()->createService('User.UserService');
    
    foreach ($threads as $key => $thread) {
        $temp_user = $UserService->getUser($thread['userId']);
        $threads[$key]['nickname'] = $temp_user['nickname'];
        $threads[$key]['smallAvatar'] = parseUrl($temp_user['smallAvatar']);
        $threads[$key]['mediumAvatar'] = parseUrl($temp_user['mediumAvatar']);
        $threads[$key]['largeAvatar'] = parseUrl($temp_user['largeAvatar']);
    }
    $count = $threadService->searchPostsCount($conditions);
    $data = array();
    $data['activity'] = filter($activity, 'activity');
    $data['threads'] = $threads;
    $data['count'] = $count;
    
    return $data;
});
//4.1参与活动
$api->get('/{articleId}/like', function (Request $request, $articleId) {
    //获取当前参与活动的人数
   
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $temp_count = $ArticleService->getArticleLikesCountByArticleId($articleId);
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $article = $ArticleService->getArticle($articleId);
    if($temp_count>=$article['max'] && $article['max']>0){
        return array('error'=>'已经达到报名人数上限');
    }
     
    $user = getCurrentUser();
    $userId = $request->query->get('userId', $user['id']);
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $result = $ArticleService->likeArticleByUserId($articleId, $userId);
    
    return $result;
});
//4.2取消参与
$api->get('/{articleId}/cancelLike', function (Request $request, $articleId) {
    $user = getCurrentUser();
    $userId = $request->query->get('userId', $user['id']);
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $result = $ArticleService->cancelLikeArticleByUserId($articleId, $userId);
    $status = array();
    $status['code'] = $result;
    
    return $status;
});
//5(发表一个评论)
$api->post('/{id}/post', function (Request $request, $id) {
    $fields = $request->request->all();
    $post['content'] = $fields['content'];
    $post['targetType'] = 'article';
    $post['targetId'] = $id;
    $post['pic'] = parseUrl($fields['pic']);
    $threadService = ServiceKernel::instance()->createService('Thread.ThreadService');
    $post = $threadService->createPost($post);

    // 1.获取文章类型，线上还是线下
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $article = $ArticleService->getArticle($id);
    $categoryId = $article['categoryId'];

    // 2.如果线上
    if ($categoryId == 5){
        $ArticleService->isLike($id);
    }

    return $post;
});
//6 获得当前用户参与的活动
$api->get('/likes/', function (Request $request) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $user = getCurrentUser();
    $userId = $request->query->get('userId', $user['id']);
    $categoryId = $request->query->get('categoryId', 0);
    $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
    $articleLikes = $ArticleService->searchArticleLikesByUserId($userId,$start,$limit);
    $articles = array();
    if (!empty($articleLikes)) {
        $articleIds = ArrayToolkit::column($articleLikes, 'articleId');
        if (!empty($articleIds)) {
            $articles = $ArticleService->findArticlesByIdsAndCatId($articleIds, $categoryId);
            $fields = array();
            $fields[] = 'id';
            $fields[] = 'title';
            $fields[] = 'categoryId';
            $fields[] = 'userId';
            //添加报名人数
            $ArticleService = ServiceKernel::instance()->createService('Article.ArticleService');
            $parts = array();
            $parts[] = 'id';
            $parts[] = 'title';
            $parts[] = 'categoryId';
            $parts[] = 'enroll';
            $parts[] = 'detail_thumb';
            $parts[] = 'detail_originalThumb';
            $parts[] = 'activity_type';
            if (!empty($articles)) {
                
                foreach ($articles as $key => $article) {
                    $temp_count = $ArticleService->getArticleLikesCountByArticleId($article['id']);
                    $article['enroll'] = $temp_count;
                    $article['detail_thumb'] = parseUrl($article['detail_thumb']);
                    $article['detail_originalThumb'] = parseUrl($article['detail_originalThumb']);
                    $articles[$key] = ArrayToolkit::parts($article, $parts);
                }
            }
        }
    }
    $data=array();
    $data['articles']=$articles;
    $data['count']=count($articles);
    
    return $data;
});

return $api;
