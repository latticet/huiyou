<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-22 14:56:16
 * @version $Id$
 */
namespace Homework\Service\Homework;

interface HomeworkTeacherService
{
public function getHomeworkTeacher($id);
public function createHomeworkTeacher($data);

public function findTeacherHomeworkByUserId($user_id,$homework_id,$homework_member_id);
public function findHomeworkByUserIdAndLessonId($user_id,$lesson_id);
public function searchHomeworkTeachers($conditions, $orderBy, $start, $limit);
public function searchHomeworkTeachersCount(array $conditions);
public function updateHomeworkTeacher($id,$HomeworkTeacher);
public function findHomeworkTeachersByLessonId($lessonId);
public function getHomeworkTeacherByHomeworkMemberId($homework_member_id);
public function delete($id);
}