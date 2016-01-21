<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-04 15:52:49
 * @version $Id$
 */

namespace Topxia\Service\Course;

interface ZcHomeworkService
{
	public function uploadHomework($Homework);

	public function deleteHomework($courseId, $HomeworkId);

	public function deleteHomeworkByHomeworkId($HomeworkId);

	public function deleteHomeworksByLessonId($lessonId);

	public function deleteHomeworksByCourseId($courseId);

	public function getHomework($courseId, $HomeworkId);

	public function findCourseHomeworks($courseId, $start, $limit);

	public function findLessonHomeworks($lessonId, $start, $limit);

	public function findHomeworksByCopyIdAndLockedCourseIds($pId, $courseIds);

	public function getHomeworkCount($courseId);

	public function getHomeworkCountByFileId($fileId);
}