<?php
namespace Homework\Service\Homework\Impl;

use Topxia\Service\Common\BaseService;
use Homework\Service\Homework\ExerciseService;

class ExerciseServiceImpl extends BaseService implements ExerciseService
{
    protected function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.ExerciseDao');
    }
    public function findExercisesByLessonIds() {
    	return array('exercise');
    }
   public function getExerciseByLessonId($lessonId){
   	
   }
}