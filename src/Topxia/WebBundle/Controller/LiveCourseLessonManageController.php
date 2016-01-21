<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Util\EdusohoLiveClient;

class LiveCourseLessonManageController extends BaseController {
    public function createAction(Request $request, $id) {
        $liveCourse = $this->getCourseService()->tryManageCourse($id);
        $parentId = $request->query->get('parentId');
        if ($request->getMethod() == 'POST') {
            $liveLesson = $request->request->all();
            if ($liveLesson['media']) {
                $liveLesson['media'] = json_decode($liveLesson['media'], true);
            }
            $liveLesson['type'] = 'live';
            $liveLesson['courseId'] = $liveCourse['id'];
            $liveLesson['startTime'] = strtotime($liveLesson['startTime']);
            $liveLesson['length'] = $liveLesson['timeLength'];
            $speakerId = current($liveCourse['teacherIds']);
            $speaker = $speakerId ? $this->getUserService()->getUser($speakerId) : null;
            $speaker = $speaker ? $speaker['nickname'] : '老师';
            $liveLogo = $this->getSettingService()->get('course');
            $liveLogoUrl = "";
            if (!empty($liveLogo) && array_key_exists("live_logo", $liveLogo) && !empty($liveLogo["live_logo"])) {
                $liveLogoUrl = $this->getServiceKernel()->getEnvVariable('baseUrl') . "/" . $liveLogo["live_logo"];
            }
            //替换掉原来的直接 来个假直播
            // $client = new EdusohoLiveClient();
            // $live = $client->createLive(array(
            //     'summary' => $liveLesson['summary'],
            //     'title' => $liveLesson['title'],
            //     'speaker' => $speaker,
            //     'startTime' => $liveLesson['startTime'] . '',
            //     'endTime' => ($liveLesson['startTime'] + $liveLesson['length']*60) . '',
            //     'authUrl' => $this->generateUrl('live_auth', array(), true),
            //     'jumpUrl' => $this->generateUrl('live_jump', array('id' => $liveLesson['courseId']), true),
            //     'liveLogoUrl' => $liveLogoUrl
            // ));
            if (empty($live)) {
                // throw new \RuntimeException('创建直播教室失败，请重试！');
                
            }
            if (isset($live['error'])) {
                // throw new \RuntimeException($live['error']);
                
            }
            $liveLesson = $this->getCourseService()->createLesson($liveLesson);
            
            return $this->render('TopxiaWebBundle:CourseLessonManage:list-item.html.twig', array(
                'course' => $liveCourse,
                'lesson' => $liveLesson,
            ));
        }
        $course = $liveCourse;
        $targetType = 'courselesson';
        $targetId = $liveCourse['id'];
        $setting = $this->setting('storage');
        $tpl = 'TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig';
        $assignBox = array();
        $assignBox['liveCourse'] = $liveCourse;
        $assignBox['parentId'] = $parentId;
        $assignBox['lesson'] = '';
        $assignBox['course'] = $course;
        $assignBox['targetType'] = $targetType;
        $assignBox['targetId'] = $targetId;
        $assignBox['storageSetting'] = $setting;
        // $assignBox['storageSetting']=$setting;
        // $assignBox['storageSetting']=$setting;
        // $assignBox['storageSetting']=$setting;
        // $assignBox['storageSetting']=$setting;
        // 'course' => $course,
        //             'targetType' => $targetType,
        //             'targetId' => $targetId,
        //             'filePath' => $filePath,
        //             'fileKey' => $fileKey,
        //             'convertKey' => $convertKey,
        //             'storageSetting' => $setting,
        //             'features' => $features,
        //             'parentId'=>$parentId,
        //             'draft' => $draft
        
        return $this->render($tpl, $assignBox);
    }
    public function editAction(Request $request, $courseId, $lessonId) {
        $liveCourse = $this->getCourseService()->tryManageCourse($courseId);
        $liveLesson = $this->getCourseService()->getCourseLesson($liveCourse['id'], $lessonId);
        $setting = $this->setting('storage');
        $targetId = $courseId;
        $targetType = 'live';

        if ($request->getMethod() == 'POST') {
            $editLiveLesson = $request->request->all();

if ($editLiveLesson['media']) {
                $editLiveLesson['media'] = json_decode($editLiveLesson['media'], true);
            
            $liveLesson['mediaSource'] = $editLiveLesson['media']['source'];
            $liveLesson['mediaName'] = $editLiveLesson['media']['name'];
            $liveLesson['mediaUri'] = $editLiveLesson['media']['uri'];
          
            }
           
            $liveLesson['type'] = 'live';
            $liveLesson['title'] = $editLiveLesson['title'];
            $liveLesson['summary'] = $editLiveLesson['summary'];
            $liveLesson['courseId'] = $liveCourse['id'];
            $liveLesson['startTime'] = empty($editLiveLesson['startTime']) ? $liveLesson['startTime'] : strtotime($editLiveLesson['startTime']);
            $liveLesson['free'] = empty($editLiveLesson['free']) ? 0 : $editLiveLesson['free'];
            $liveLesson['length'] = empty($editLiveLesson['timeLength']) ? $liveLesson['length'] : $editLiveLesson['timeLength'];
            $speakerId = current($liveCourse['teacherIds']);
            $speaker = $speakerId ? $this->getUserService()->getUser($speakerId) : null;
            $speaker = $speaker ? $speaker['nickname'] : '老师';
            $liveParams = array(
                'liveId' => $liveLesson['mediaId'],
                'provider' => $liveLesson['liveProvider'],
                'summary' => $editLiveLesson['summary'],
                'title' => $editLiveLesson['title'],
                'speaker' => $speaker,
                'authUrl' => $this->generateUrl('live_auth', array() , true) ,
                'jumpUrl' => $this->generateUrl('live_jump', array(
                    'id' => $liveLesson['courseId']
                ) , true) ,
            );
            if (array_key_exists('startTime', $editLiveLesson)) {
                $liveParams['startTime'] = strtotime($editLiveLesson['startTime']);
            }
            if (array_key_exists('startTime', $editLiveLesson) && array_key_exists('timeLength', $editLiveLesson)) {
                $liveParams['endTime'] = (strtotime($editLiveLesson['startTime']) + $editLiveLesson['timeLength'] * 60) . '';
            }
            // $client = new EdusohoLiveClient();
            // $live = $client->updateLive($liveParams);
            $liveLesson = $this->getCourseService()->updateLesson($courseId, $lessonId, $liveLesson);
            
           
            $tpl = 'TopxiaWebBundle:CourseLessonManage:list-item.html.twig';
        $assignBox = array();
            $assignBox['course'] = $liveCourse;
            $assignBox['lesson'] = $liveLesson;
            $assignBox['storageSetting'] = $setting;
            $assignBox['targetType'] = $targetType;
            $assignBox['targetId'] = $targetId;
            $assignBox['course'] = $liveCourse;
             $assignBox['lesson'] = $liveLesson;
            return $this->render($tpl, $assignBox);
        }
         $tpl = 'TopxiaWebBundle:LiveCourseLessonManage:live-lesson-modal.html.twig';
        $assignBox = array();
            $assignBox['liveCourse'] = $liveCourse;
            $assignBox['liveLesson'] = $liveLesson;
            $assignBox['storageSetting'] = $setting;
           $assignBox['targetType'] = $targetType;
           $assignBox['targetId'] = $targetId;
            $assignBox['course'] = $liveCourse;
            $assignBox['lesson'] = $liveLesson;
            return $this->render($tpl, $assignBox);

    }
    public function lessonTimeCheckAction(Request $request, $id) {
        $data = $request->query->all();
        $startTime = $data['startTime'];
        $length = $data['length'];
        $lessonId = empty($data['lessonId']) ? "" : $data['lessonId'];
        list($result, $message) = $this->getCourseService()->liveLessonTimeCheck($id, $lessonId, $startTime, $length);
        if ($result == 'success') {
            $response = array(
                'success' => true,
                'message' => '这个时间段的课时可以创建'
            );
        } else {
            $response = array(
                'success' => false,
                'message' => $message
            );
        }
        
        return $this->createJsonResponse($response);
    }
    public function calculateLeftCapacityAction(Request $request, $courseId) {
        $data = $request->query->all();
        $startTime = strtotime($data['startTime']);
        $length = $data['length'];
        $endTime = $startTime + $length * 60;
        $lessonId = empty($data['lessonId']) ? "" : $data['lessonId'];
        $leftCapacity = $this->getCourseService()->calculateLiveCourseLeftCapacityInTimeRange($startTime, $endTime, $lessonId);
        
        return $this->createJsonResponse($leftCapacity);
    }
    protected function getCourseService() {
        
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    protected function getSettingService() {
        
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
