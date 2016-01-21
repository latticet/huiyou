<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-22 14:56:16
 * @version $Id$
 */
namespace Homework\Service\Homework;

interface HomeworkMemberService
{
public function getHomeworkMember($id);
public function createHomeworkMember($data);
public function findStudentHomeworkByUserId($user_id,$homework_id);

public function findHomeworkByUserIdAndLessonId($user_id,$lesson_id);
public function searchHomeworkMembers($conditions, $orderBy, $start, $limit);
public function searchHomeworkMembersCount(array $conditions);
public function updateHomeworkMember($id,$homeworkMember);
public function findHomeworkMembersByLessonId($lessonId);
public function delete($id);

}