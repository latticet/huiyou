<?php
namespace Homework\HomeworkBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;

class HomeworkController extends \Topxia\WebBundle\Controller\BaseController {
    public function indexAction(Request $request, $courseId) {
        $course = $this->getCourseService()->getCourse($courseId);
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        $lessons = ArrayToolkit::index($lessons, 'homeworkId');
        $homeworks = array();
        $homeworkService = $this->getHomeworkService();
        $conditions = array();
        $conditions['course_id'] = $course['id'];
        $orderBy = array();
        $orderBy[] = 'create_at';
        $orderBy[] = 'DESC';
        $count = $homeworkService->searchHomeworksCount($conditions);
        $page_size = 20;
        $paginator = new Paginator($request, $count, $page_size);
        $start = $paginator->getOffsetCount();
        $limit = $paginator->getPerPageCount();
        $homeworks = $homeworkService->searchHomeworks($conditions, $orderBy, $start, $limit);
        $homeworks = $this->addLessonName($homeworks, $lessons);
        $storageSetting = $this->getSettingService()->get("storage");
        $tpl = 'HomeworkBundle:Homework:index.html.twig';
        $assignBox = array();
        $assignBox['course'] = $course;
        $assignBox['homeworks'] = $homeworks;
        $assignBox['paginator'] = $paginator;
        $assignBox['now'] = $_SERVER['REQUEST_TIME'];
        $assignBox['storageSetting'] = $storageSetting;
        
        return $this->render($tpl, $assignBox);
    }
    public function getLessonNames() {
    }
    /**
     * 工具栏右侧作业列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function listPluginAction(Request $request) {
        $homeworkService = $this->getHomeworkService();
        $lessonId = $request->query->get('lessonId', 0);
        $courseId = $request->query->get('courseId', 0);
        $course = ServiceKernel::instance()->createService('Course.CourseService')->getCourse($courseId);
        $lesson = ServiceKernel::instance()->createService('Course.CourseService')->getCourseLesson($courseId, $lessonId);
        $homeworks = array();
        $homework = $homeworkService->findHomeworkByLessonId($lessonId);
        if (!empty($homework)) {
            $homeworks[] = $homework;
        }
        $tpl = 'HomeworkBundle:Homework:plugin-list.html.twig';
        $assignBox = array();
        $assignBox['homeworks'] = $homeworks;
        $assignBox['course'] = $course;
        $assignBox['lesson'] = $lesson;
        
        return $this->render($tpl, $assignBox);
    }
    public function lessonHomeworkListAction(Request $request, $courseId, $lessonId) {
        $course = ServiceKernel::instance()->createService('Course.CourseService')->getCourse($courseId);
        $lesson = ServiceKernel::instance()->createService('Course.CourseService')->getCourseLesson($courseId, $lessonId);
        $homeworkService = $this->getHomeworkService();
        $homework = $homeworkService->findHomeworkByLessonId($lessonId);
        $homeworkMemberService = $this->getHomeworkMemberService();
        $homework_members = $homeworkMemberService->findHomeworkMembersByLessonId($lessonId);
        $conditions = array();
        $conditions['lesson_id'] = $lesson['id'];
        $orderBy = array();
        $orderBy[] = 'create_at';
        $orderBy[] = 'DESC';
        $count = $homeworkMemberService->searchHomeworkMembersCount($conditions);
        $page_size = 20;
        $paginator = new Paginator($request, $count, $page_size);
        $start = $paginator->getOffsetCount();
        $limit = $paginator->getPerPageCount();
        $homework_members = $homeworkMemberService->searchHomeworkMembers($conditions, $orderBy, $start, $limit);
        $homework_members = $this->addUser($homework_members);
        $tpl = 'HomeworkBundle:Homework:lesson-homework-list.html.twig';
        $assignBox = array();
        $assignBox['course'] = $course;
        $assignBox['lesson'] = $lesson;
        $assignBox['homework'] = $homework;
        $assignBox['homework_members'] = $homework_members;
        $assignBox['paginator'] = $paginator;
        
        return $this->render($tpl, $assignBox);
    }
    public function addAction(Request $request, $courseId, $lessonId) {
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $homeworkService = $this->getHomeworkService();
        $user = $this->getCurrentUser();
        if ($request->getMethod() == 'POST') {
            $detail = $request->request->all();
            if (isset($detail['homework_id']) && $detail['homework_id'] > 0) {
                $homework_id = $detail['homework_id'];
                $data = array();
                $data['title'] = $detail['content'];
                $data['content'] = $detail['content'];
                $homework = $homeworkService->updateHomework($homework_id, $data);
                //向课程学生发送提醒
                $this->sendNotifications($homework['id']);
            } else {
                unset($detail['homework_id']);
                $detail['course_id'] = $courseId;
                $detail['lesson_id'] = $lessonId;
                $detail['user_id'] = $user['id'];
                $detail['title'] = $detail['content'];
                $homework = $homeworkService->createHomework($detail);
                //更新homeworkId 作业绑定章节
                $fields = array();
                $fields['homeworkId'] = $homework['id'];
                $this->getCourseService()->updateLesson($courseId, $lessonId, $fields);
            }
        } else {
            $homework = $homeworkService->findHomeworkByLessonId($lessonId);
        }
        if (empty($homework)) {
            $homework = array();
            $homework['id'] = 0;
            $homework['content'] = '';
        }
        $tpl = 'HomeworkBundle:Homework:add-homework-modal.html.twig';
        $assignBox = array();
        $assignBox['homework'] = $homework;
        $assignBox['course'] = $course;
        $assignBox['lesson'] = $lesson;
        $assignBox['targetType'] = 'homeworkPic';
        $assignBox['targetId'] = $course['id'];
        $assignBox['storageSetting'] = $this->setting('storage');
        
        return $this->render($tpl, $assignBox);
    }
    public function doAction(Request $request, $courseId, $lessonId) {
        $user = $this->getCurrentUser();
        $userService = $this->getUserService();
        $member_type = '';
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $homeworkService = $this->getHomeworkService();
        if ($request->getMethod() == 'POST') {
            $detail = $request->request->all();
            if (isset($detail['homework_id']) && $detail['homework_id'] > 0) {
                $homework_id = $detail['homework_id'];
                $data = array();
                $data['content'] = $detail['content'];
                $homework = $homeworkService->updateHomework($homework_id, $data);
            } else {
                unset($detail['homework_id']);
                $detail['course_id'] = $courseId;
                $detail['lesson_id'] = $lessonId;
                $homework = $homeworkService->createHomework($detail);
            }
        } else {
            $homework = $homeworkService->findHomeworkByLessonId($lessonId);
        }
        $is_teacher = false;
        $is_teacher = in_array('ROLE_TEACHER', $user['roles']);
        $homework_member = array();
        if ($is_teacher) {
            $member_type = 'teacher';
            $studentId = $request->query->get('studentId');
            $teacherId = $user['id'];
            $student_info = $userService->getUser($studentId);
            $teacher_info = $user;
        } else {
            $member_type = 'student';
            $studentId = $user['id'];
            $teacherId = $homework['user_id'];
            $student_info = $user;
            $teacher_info = $userService->getUser($teacherId);
        }
        $homeworkMemberService = $this->getHomeworkMemberService();
        $homework_student = $homeworkMemberService->findStudentHomeworkByUserId($studentId, $lesson['homeworkId']);
        $homework_student['pic_path'] = $this->picParse($homework_student['pic']);
        if (!isset($homework_student['remark'])) {
            $homework_student['remark'] = '';
        }
        $homework_member['student'] = $homework_student;
        if (empty($homework)) {
            $homework = array();
            $homework['id'] = 0;
            $homework['content'] = '';
        }
        $tpl = 'HomeworkBundle:Homework:do-homework.html.twig';
        $assignBox = array();
        $assignBox['homework'] = $homework;
        $assignBox['course'] = $course;
        $assignBox['lesson'] = $lesson;
        $assignBox['targetType'] = 'homeworkPic';
        $assignBox['targetId'] = $course['id'];
        $assignBox['storageSetting'] = $this->setting('storage');
        $assignBox['homework_member'] = $homework_member;
        $assignBox['member_type'] = $member_type;
        
        return $this->render($tpl, $assignBox);
    }
    public function isHomeworkExists() {
    }
    public function saveAction(Request $request) {
        
        return $this->render('HomeworkBundle:Homework:add.html.twig', array(
            'name' => $name
        ));
    }
    public function deleteAction(Request $request) {
        $homeworkMemberService = $this->getHomeworkMemberService();
        $ids = $request->request->all();
        if (!empty($ids)) {
            
            foreach ($ids as $key => $homework_id_arr) {
                $homework_id = $homework_id_arr[0];
                $conditions = array();
                $conditions['homework_id'] = $homework_id;
                $count = $homeworkMemberService->searchHomeworkMembersCount($conditions);
                if ($count > 0) {
                    $response = array();
                    $response['message'] = '已经有学生提交了相应的作业,请先删除相关内容!';
                    
                    return $this->createJsonResponse($response);
                }
                $this->deleteHomework($homework_id);
            }
        }
        
        return $this->createJsonResponse(true);
    }
    public function deleteHomework($homework_id) {
        $homeworkService = $this->getHomeworkService();
        $homework = $homeworkService->getHomework($homework_id);
        if (!empty($homework)) {
            $homeworkService->delete($homework_id);
        }
        
        return true;
    }
    public function uploadHomeworkFileAction(Request $request, $courseId) {
        $course = $this->getCourseService()->getCourse($courseId);
        $paginator = new Paginator($request, 100, 20);
        $assignBox = array();
        $assignBox['site']['logo'] = '/files/system/2015/11-15/112251b78d20251961.jpg';
        $assignBox['type'] = '';
        $assignBox['course'] = $course;
        $assignBox['courseLessons'] = '';
        $assignBox['users'] = $course;
        $assignBox['paginator'] = $paginator;
        $assignBox['now'] = time();
        $assignBox['storageSetting'] = '';
        
        return $this->render('HomeworkBundle:Homework:homework-list.html.twig', $assignBox);
    }
    public function logoUploadAction(Request $request) {
        $fileId = $request->request->get('id');
        $objectFile = $this->getFileService()->getFileObject($fileId);
        if (!FileToolkit::isImageFile($objectFile)) {
            throw $this->createAccessDeniedException('图片格式不正确！');
        }
        $file = $this->getFileService()->getFile($fileId);
        $parsed = $this->getFileService()->parseFileUri($file["uri"]);
        $response = array(
            'path' => '/files/system/2015/11-15/112251b78d20251961.jpg',
            'url' => '/files/system/2015/11-15/112251b78d20251961.jpg',
        );
        
        return $this->createJsonResponse($response);
    }
    protected function getHomeworkService() {
        
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }
    protected function getHomeworkMemberService() {
        
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkMemberService');
    }
    protected function getHomeworkTeacherService() {
        
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkTeacherService');
    }
    protected function getCourseService() {
        
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    protected function getUserService() {
        
        return $this->getServiceKernel()->createService('User.UserService');
    }
    protected function getFileService() {
        
        return $this->getServiceKernel()->createService('Content.FileService');
    }
    protected function getSettingService() {
        
        return $this->getServiceKernel()->createService('System.SettingService');
    }
    protected function addLessonName($homeworks, $lessons) {
        if (!empty($homeworks)) {
            
            foreach ($homeworks as $key => $homework) {
                if (isset($lessons[$homework['id']])) {
                    $homeworks[$key]['lesson_name'] = $lessons[$homework['id']]['title'];
                    $homeworks[$key]['lesson_id'] = $lessons[$homework['id']]['id'];
                } else {
                    $homeworks[$key]['lesson_name'] = '';
                    $homeworks[$key]['lesson_id'] = '';
                }
            }
        }
        
        return $homeworks;
    }
    protected function addUser($arr) {
        if (!empty($arr)) {
            
            foreach ($arr as $key => $value) {
                if (isset($value['user_id'])) {
                    $arr[$key]['user_info'] = $this->getUserService()->getUser($value['user_id']);
                }
            }
        }
        
        return $arr;
    }
    protected function picParse($pic) {
        $url = '';
        if (!empty($pic)) {
            $public_mark = 'public:/';
            $public_path = '/files';
            $private_mark = 'private:/';
            $private_path = '/app/data/private_files';
            if (strpos($pic, $public_mark) !== false) {
                $url = str_replace($public_mark, $public_path, $pic);
            } elseif (strpos($pic, $private_mark) !== false) {
                $url = str_replace($private_mark, $private_path, $pic);
            }
        }
        
        return $url;
    }
    protected function getRootPath() {
        $appRoot = $this->get('kernel')->getRootDir(); // 这里得到的是app目录的绝对路径
        $root = dirname($appRoot);
        
        return $root;
    }
    protected function sendNotifications($homework_id) {
        $homework = $this->getHomeworkService()->getHomework($homework_id);
        $lessonId = $homework['lesson_id'];
        $learns = $this->getCourseService()->findLearnsByLessonId($lessonId, 0, 9999);
        $userIds = ArrayToolkit::column($learns, 'userId');
        $type = 'homework';
        $teacher = $this->getUserService()->getUser($homework['user_id']);
        $course = $this->getCourseService()->getCourse($homework['course_id']);
        $lesson = $this->getCourseService()->getLesson($homework['lesson_id']);
        $user = $this->getCurrentUser();
        $url = "/api/homework/{$homework['id']}";
        $content = "{$user['nickname']}为{$course['title']}-{$lesson['title']}布置了作业,快去看看吧 {$url}";
        
        foreach ($userIds as $key => $userId) {
            $this->getNotificationService()->notify($userId, $type, $content);
        }
    }
    protected function getNotificationService() {
        
        return $this->getServiceKernel()->createService('User.NotificationService');
    }
}
