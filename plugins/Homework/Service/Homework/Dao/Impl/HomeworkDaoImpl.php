<?php

namespace Homework\Service\Homework\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Homework\Service\Homework\Dao\HomeworkDao;

class HomeworkDaoImpl extends BaseDao implements HomeworkDao
{


	protected $table = 'homework';

	public function addHomework($Homework) 
    {
		$affected = $this->getConnection()->insert($this->table, $Homework);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Homework error.');
        }
        return $this->getHomework($this->getConnection()->lastInsertId());
	}

	public function deleteHomework($id) 
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function getHomework($id) 
    {
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
	}
    public function findHomeworkByLessonId($lessonId) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE lesson_id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($lessonId));
    }


    public function getHomeworkByParentId($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($parentId));
    }

    public function findAllCategoriesByParentId($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? order by weight";
        return $this->getConnection()->fetchAll($sql, array($parentId));
    }
    
	public function findHomeworkByCode($code) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code));
	}

	public function updateHomework($id, $Homework) 
    {
        $this->getConnection()->update($this->table, $Homework, array('id' => $id));
        return $this->getHomework($id);
	}

	public function findCategoriesByParentId($parentId, $orderBy, $start, $limit) 
    {
        $this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE parentId = ? ORDER BY {$orderBy} DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($parentId)) ? : array();
	}

	public function findCategoriesCountByParentId($parentId) 
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  parentId = ?";
        return $this->getConnection()->fetchColumn($sql, array($parentId));
	}

	public function findCategoriesByIds(array $ids) 
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids) ? : array();
    }

    public function findAllCategories()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY weight ASC";
        return $this->getConnection()->fetchAll($sql) ? : array();
    }
    public function searchHomeworks($conditions, $orderBy, $start, $limit)
    {
        
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->from($this->table)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);       
       
        return $builder->execute()->fetchAll() ? : array(); 
    }
    public function searchHomeworksCount(array $conditions){

        $builder = $this->_createSearchQueryBuilder($conditions)->from($this->table)
            
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
   
    }
    public function delete($id){
return $this->getConnection()->delete($this->table, array('id' => $id));;
    }   

        protected function _createSearchQueryBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'homework')
            ->andWhere('course_id IN (:course_id)');

        return $builder;
    }
}