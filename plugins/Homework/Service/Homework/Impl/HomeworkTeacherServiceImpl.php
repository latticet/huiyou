<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-22 14:57:05
 * @version $Id$
 */

namespace Homework\Service\Homework\Impl;
use Topxia\Service\Common\BaseService;
use Homework\Service\Homework\HomeworkTeacherService;
use Topxia\Common\ArrayToolkit;

class HomeworkTeacherServiceImpl extends BaseService implements HomeworkTeacherService {
    protected function getHomeworkTeacherDao() {
        
        return $this->createDao('Homework:Homework.HomeworkTeacherDao');
    }
    public function getHomeworkTeacher($id){
        return $this->getHomeworkTeacherDao()->getHomeworkTeacher($id);
    }
    
    public function findTeacherHomeworkByUserId($user_id,$homework_id,$homework_member_id){
        return $this->getHomeworkTeacherDao()->findTeacherHomeworkByUserId($user_id,$homework_id,$homework_member_id);
    }
    public function findHomeworkByUserIdAndLessonId($user_id,$lesson_id)
    {
        return $this->getHomeworkTeacherDao()->findHomeworkByUserIdAndLessonId($user_id,$lesson_id);
    }
    public function createHomeworkTeacher($data){
    	return $this->getHomeworkTeacherDao()->createHomeworkTeacher($data);
    }
    public function searchHomeworkTeachers($conditions, $orderBy, $start, $limit)
    {
     return $this->getHomeworkTeacherDao()->searchHomeworkTeachers($conditions, $orderBy, $start, $limit);   
    }
    public function searchHomeworkTeachersCount(array $conditions){

       return $this->getHomeworkTeacherDao()->searchHomeworkTeachersCount($conditions);
   
    }
    public function updateHomeworkTeacher($id,$HomeworkTeacher)
    {
        return $this->getHomeworkTeacherDao()->updateHomeworkTeacher($id,$HomeworkTeacher);
    }
    public function findHomeworkTeachersByLessonId($lessonId){
        
    }
    public function getHomeworkTeacherByHomeworkMemberId($homework_member_id)
    {
        return $this->getHomeworkTeacherDao()->getHomeworkTeacherByHomeworkMemberId($homework_member_id);
    }
    public function delete($id) {
        return $this->getHomeworkTeacherDao()->delete($id);
    }
}