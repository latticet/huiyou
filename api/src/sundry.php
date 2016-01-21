<?php
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
$api = $app['controllers_factory'];
//1.1 消息推送 获取用户短消息列表
$api->get('/message/list', function (Request $request) {
  $user = getCurrentUser();
  $userId = $request->query->get('userId', $user['id']);
  $start = $request->query->get('start', 0);
  $limit = $request->query->get('limit', 10);
  $MessageService = ServiceKernel::instance()->createService('User.MessageService');
  $conversations = $MessageService->findUserConversations($userId, $start, $limit);
  $count = $MessageService->getUserConversationCount($userId);
  $conversations = filters($conversations, 'sundry');
  $data = array();
  $data['list'] = $conversations;
  $data['count'] = $count;
  
  return $data;
});
//1.2 获取对话列表
$api->get('/message/{conversationId}', function (Request $request, $conversationId) {
  $start = $request->query->get('start', 0);
  $limit = $request->query->get('limit', 10);
  $MessageService = ServiceKernel::instance()->createService('User.MessageService');
  $conversations = $MessageService->findConversationMessages($conversationId, $start, $limit);
  $count = $MessageService->getConversationMessageCount($conversationId);
  $conversations = filters($conversations, 'sundry');
  $data = array();
  $data['list'] = $conversations;
  $data['count'] = $count;
  
  return $data;
});
//2意见与建议
$api->post('/message/post', function (Request $request) {
  $user = getCurrentUser();
  $userId = $request->query->get('userId', $user['id']);
  $content = $request->request->get('content');
  $fromId = $userId;
  $toId = 1;
  $type = 'text';
  $createdTime = time();
  $MessageService = ServiceKernel::instance()->createService('User.MessageService');
  $message = $MessageService->sendMessage($fromId, $toId, $content, $type, $createdTime);
  
  return $message;
});
//3关于我们
$api->get('/content/aboutus', function () {
  $alias ='aboutus';
  $ContentService = ServiceKernel::instance()->createService('Content.ContentService');
  $aboutus=$ContentService->getContentByAlias($alias);
  
  
  return filter($aboutus, 'sundry');
});
//获取所有自定义页面信息
$api->get('/content/page', function () {
  $ContentService = ServiceKernel::instance()->createService('Content.ContentService');
  $start = 0;
  $limit = 9999;
  $orderBy = array(
    'id',
    'DESC'
  );
  $conditions = array();
  $conditions['type'] = 'page';
  $contents = $ContentService->searchContents($conditions, $orderBy, $start, $limit);
  
  return filters($contents, 'sundry');
});

return $api;
