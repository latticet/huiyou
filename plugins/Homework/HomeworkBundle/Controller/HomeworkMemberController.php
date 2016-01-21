<?php
namespace Homework\HomeworkBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeworkMemberController extends HomeworkController {
	public function deleteAction(Request $request) {

		$ids = $request->request->all();
		if (!empty($ids)) {			
			foreach ($ids as $key => $homework_member_id_arr) {
				$homework_member_id = $homework_member_id_arr[0];
				$this->deleteMemberAndTeacher($homework_member_id);				
			}
		}
		
		return $this->createJsonResponse(true);
	}
	protected function deleteMemberAndTeacher($homework_member_id) {
		$homeworkMemberService = $this->getHomeworkMemberService();
		$homework_member = $homeworkMemberService->getHomeworkMember($homework_member_id);
		if (!empty($homework_member)) {
			$homeworkTeacherService = $this->getHomeworkTeacherService();
			$homework_teacher = $homeworkTeacherService->getHomeworkTeacherByHomeworkMemberId($homework_member['id']);
			if (!empty($homework_teacher)) {
				//删除教师批改
				$homeworkTeacherService->delete($homework_teacher['id']);
				$teacher_path = $this->picParse($homework_teacher['pic']);
				$this->deleteFile($teacher_path);
				
			}
			//删除学生提交
			$homeworkMemberService->delete($homework_member['id']);
				$member_path = $this->picParse($homework_member['pic']);
				$this->deleteFile($member_path);
			
		}
	}
	protected function deleteFile($path) {
		$root = $this->getRootPath();
		$path = $root.'/web'.$path;
		if (file_exists($path) && is_file($path)) {
			unlink($path);			
			return true;
		} else {			
			return false;
		}
	}
}
