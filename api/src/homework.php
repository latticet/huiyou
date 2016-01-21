<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-18 15:46:56
 * @version $Id$
 */

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;

$api = $app['controllers_factory'];

//根据id获取一个课程作业信息
$api->get('/{id}', function ($id) {
    $homework = convert($id,'homework');    
    return filter($homework, 'homework');
});
//获取课程作业列表
$api->get('/course/{courseId}', function (Request $request,$courseId) {
    $start = $request->query->get('start', 0);
    $limit = $request->query->get('limit', 10);
    $conditions=array();
    $orderBy=array('id','DESC');
    $homeworks = ServiceKernel::instance()->createService('Homework:Homework.HomeworkService')->searchHomeworks($conditions,$orderBy , $start, $limit);    
    return filters($homeworks, 'homework');
});
//学生提交作业
$api->post('/{homeworkId}/student/{studentId}', function (Request $request,$homeworkId,$studentId) {

        $homework = convert($homeworkId,'homework');
        $homework_id = $homework['id'];
        $lesson_id=$homework['lesson_id']; 
        $member_type = $request->request->get('member_type');
        $remark = $request->request->get('remark');

        $groupCode='homework'; 
        $type = 'image';        
        $file = $request->files->get('file');        
        if ($type == 'image') {
            if (!FileToolkit::isImageFile($file)) {
                throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
            }
        } else {
            throw new \RuntimeException("上传类型不正确！");
        }
        $record = ServiceKernel::instance()->createService('Content.FileService')->uploadFile($groupCode, $file);
        
        //保存作业记录
        $homework_member=ServiceKernel::instance()->createService('Homework:Homework.HomeworkMemberService');
       
        $data=array();
        $data['homework_id']=$homework_id;
        $data['type']=$member_type;
        $data['user_id']=$record['userId'];
        $data['lesson_id']=$lesson_id;
        $data['pic']=$record['uri'];
        $data['create_at']=$_SERVER['REQUEST_TIME'];
        $data['remark']=$remark;

        $check_data= $homework_member->findHomeworkByUserIdAndLessonId($data['user_id'],$lesson_id);
        if(!empty($check_data)){
            $homework_member= $homework_member->updateHomeworkMember($check_data['id'],$data);
        }else{
            $homework_member= $homework_member->createHomeworkMember($data);
        }
        
    return $homework_member;
});
//获取学生完成的作业信息
$api->get('/{homeworkId}/student/{studentId}', function ($homeworkId,$studentId) {
    $hService =ServiceKernel::instance()->createService('Homework:Homework.HomeworkService');
    $hmService = ServiceKernel::instance()->createService('Homework:Homework.HomeworkMemberService');
    $homework= $hService->getHomework($homeworkId);
    $homework_student = $hmService->findStudentHomeworkByUserId($studentId,$homework['id']);
    $homework_student = filter($homework_student, 'homework');
    $data=array();
    $data['homework']=$homework;
    $data['homework_student']=$homework_student; 
    return $data;
});
//获取老师批改的作业信息
$api->get('/{homeworkId}/teacher/{teacherId}', function (Request $request,$homeworkId,$teacherId) {
   $homework_member_id = $request->query->get('studentHomeworkId',0);
   $hService =ServiceKernel::instance()->createService('Homework:Homework.HomeworkService');
    $htService = ServiceKernel::instance()->createService('Homework:Homework.HomeworkTeacherService');
   
    $homework= $hService->getHomework($homeworkId);
    $homework_teacher = $htService->findTeacherHomeworkByUserId($teacherId,$homework['id'],$homework_member_id);
    
    $data=array();
    $data['homework']=$homework;
    $data['homework_teacher']=$homework_teacher; 
    return $data;
});
//获取作业+学生提交+老师批改
$api->get('/{homeworkId}/all/student/{studentId}', function ($homeworkId,$studentId) {
    $hService =ServiceKernel::instance()->createService('Homework:Homework.HomeworkService');
    $hmService = ServiceKernel::instance()->createService('Homework:Homework.HomeworkMemberService');
    $homework= $hService->getHomework($homeworkId);
    $homework_teacher = $hmService->findTeacherHomeworkByUserId($homework['user_id'],$homework['id']);
    $homework_teacher = filter($homework_teacher, 'homework');
    $homework_student = $hmService->findStudentHomeworkByUserId($studentId,$homework['id']);
    $homework_student = filter($homework_student, 'homework');
    $data=array();
    $data['homework']=$homework;
    $data['homework_teacher']=$homework_teacher; 
    $data['homework_student']=$homework_student; 
    return $data;
});

//获取我的所有作业列表
$api->get('/all/', function (Request $request) {
    $user=getCurrentUser();
    $studentId = $request->query->get('studentId',$user['id']);
    //我的课程


    $CourseService = ServiceKernel::instance()->createService('Course.CourseService');
    
    $start=$request->query->get('start',0);
    $limit=$request->query->get('limit',10);
        $courses = $CourseService->findUserLearnCourses($studentId, 0, 9999);
   
    //课程下的作业
    $course_ids = ArrayToolkit::column($courses,'id');
    
    $conditions = array();
    $conditions['course_id']=$course_ids;
    $orderBy=array('id','DESC');

    $HomeworkService = ServiceKernel::instance()->createService('Homework:Homework.HomeworkService');
    $homeworks = $HomeworkService->searchHomeworks($conditions,$orderBy , $start, $limit);
   
    $data=array();
    $data['courses']=$courses;
    $data['homeworks']=$homeworks;
    return $data;
});


return $api;