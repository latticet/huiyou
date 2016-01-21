<?php
namespace Homework\Service\Homework;

interface HomeworkService
{
public function findHomeworksByCourseIdAndLessonIds($courseId, $lessonIds);
public function findExercisesByLessonIds($lessonIds);
    public function getHomework($id);
    public function createHomework(array $Homework);
    public function searchHomeworks(array $conditions, $sort, $start, $limit);
    public function searchHomeworksCount(array $conditions);
    public function findHomeworkByLessonId($lessonId);
    public function getHomeworkByLessonId($lessonId);
    public function searchResults();
    public function uploadMaterial($material);    
    public function delete($id);


}