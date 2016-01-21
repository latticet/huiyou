<?php
namespace Homework\HomeworkBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeworkTeacherController extends HomeworkController {
	    public function correctAction(Request $request, $courseId, $lessonId) {
        $studentId = $request->query->get('userId',0);
        
        
        $userService = $this->getUserService();
        $member_type = '';
        $course = $this->getCourseService()->getCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $homeworkService = $this->getHomeworkService();
        $homework = $homeworkService->findHomeworkByLessonId($lessonId);

            $student_info = $userService->getUser($studentId);
            $teacher_info = $this->getCurrentUser();
       $teacherId = $teacher_info['id'];
        $homeworkMemberService = $this->getHomeworkMemberService();
        $homework_student = $homeworkMemberService->findStudentHomeworkByUserId($studentId, $lesson['homeworkId']);
        
        $homework_student['pic_path'] = $this->picParse($homework_student['pic']);
        $homeworkTeacherService= $this->getHomeworkTeacherService();
        $homework_teacher = $homeworkTeacherService->findTeacherHomeworkByUserId($teacherId, $lesson['homeworkId'] ,$homework_student['id']);
        if(empty($homework_teacher)){
        	$homework_teacher['pic_path']='';
        	$homework_teacher['remark']='';

        }else{
$homework_teacher['pic_path'] = $this->picParse($homework_teacher['pic']);       
    
        }
        
$homework_student['user_info'] = $student_info;

            $homework_member['student'] = $homework_student;
   $homework_teacher['user_info']= $teacher_info;        
        $homework_member['teacher'] = $homework_teacher;

        if (empty($homework)) {
            $homework = array();
            $homework['id'] = 0;
            $homework['content'] = '';
        }
        $tpl = 'HomeworkBundle:HomeworkTeacher:correct-homework-member.html.twig';
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

    public function uploadImageDataAction(Request $request){
        $post = $request->request->all();
        $image = imagecreatefrompng($post['image']);

imagealphablending($image, false);
imagesavealpha($image, true);

$web_root='./files';
$group='homework';
$year=date('Y');
$month_and_day= date('m-d');
$name=time().mt_rand(100,999).'.png';
$dir_path = "{$web_root}/{$group}/{$year}/{$month_and_day}";
if(!is_dir($dir_path)){
    mkdir($dir_path,0777,true);
}
$file_path = "{$dir_path}/{$name}";

imagepng($image, $file_path);
$message=array();
$message['message']='你好';
$pic = str_replace('./files/', 'public://', $file_path);
$message['pic']=$pic;
return $this->createJsonResponse($message);
    }

}