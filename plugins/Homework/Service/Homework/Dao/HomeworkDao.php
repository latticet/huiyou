<?php

namespace Homework\Service\Homework\Dao;

interface HomeworkDao
{

	public function addHomework($Homework);

	public function deleteHomework($id);

	public function getHomework($id);

	public function findHomeworkByCode($code);
	
	public function getHomeworkByParentId($parentId);

	public function findAllCategoriesByParentId($parentId);

	public function findAllCategories();

	public function updateHomework($id, $Homework);

	public function findCategoriesByParentId($parentId, $orderBy, $start, $limit);

	public function findCategoriesCountByParentId($parentId);

	public function findCategoriesByIds(array $ids);

	public function searchHomeworks($conditions, $orderBy, $start, $limit);
	public function searchHomeworksCount(array $conditions);
    public function delete($id);
}
