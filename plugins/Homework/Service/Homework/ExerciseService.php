<?php
namespace Homework\Service\Homework;

interface ExerciseService
{

public function findExercisesByLessonIds() ;
public function getExerciseByLessonId($lessonId);
}