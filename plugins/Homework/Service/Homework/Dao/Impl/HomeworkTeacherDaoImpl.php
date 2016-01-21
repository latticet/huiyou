<?php
/**
 *
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-22 15:00:18
 * @version $Id$
 */
namespace Homework\Service\Homework\Dao\Impl;
use Topxia\Service\Common\BaseDao;
use Homework\Service\Homework\Dao\HomeworkTeacherDao;

class HomeworkTeacherDaoImpl extends BaseDao implements HomeworkTeacherDao {
    protected $table = 'homework_teacher';
    public function getHomeworkTeacher($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->fetchAssoc($sql, array(
            $id
        ));
    }
    public function createHomeworkTeacher($data) {
        $affected = $this->getConnection()->insert($this->table, $data);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert HomeworkTeacher error.');
        }
        
        return $this->getHomeworkTeacher($this->getConnection()->lastInsertId());
    }
    public function findTeacherHomeworkByUserId($user_id, $homework_id, $homework_member_id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND homework_id = ? AND homework_member_id = ? LIMIT 1";
        $result = $this->getConnection()->fetchAll($sql, array(
            $user_id,
            $homework_id,
            $homework_member_id
        )) ? : null;
        
        if (!empty($result)) {
            
            return $result[0];
        } else {
            
            return $result;
        }
    }
    public function findHomeworkByUserIdAndLessonId($user_id, $lesson_id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND lesson_id = ?  LIMIT 1";
        $result = $this->getConnection()->fetchAll($sql, array(
            $user_id,
            $lesson_id
        )) ? : null;
        if (!empty($result)) {
            
            return $result[0];
        } else {
            
            return $result;
        }
    }
    public function searchHomeworkTeachers($conditions, $orderBy, $start, $limit) {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)->select('*')->orderBy($orderBy[0], $orderBy[1])->setFirstResult($start)->setMaxResults($limit);
        
        return $builder->execute()->fetchAll() ? : array();
    }
    public function searchHomeworkTeachersCount(array $conditions) {
        $builder = $this->_createSearchQueryBuilder($conditions)->select('COUNT(id)');
        
        return $builder->execute()->fetchColumn(0);
    }
    public function updateHomeworkTeacher($id, $HomeworkTeacher) {
        $this->getConnection()->update($this->table, $HomeworkTeacher, array(
            'id' => $id
        ));
        
        return $this->getHomeworkTeacher($id);
    }
    public function findHomeworkTeachersByLessonId($lessonId) {
        $sql = "SELECT * FROM {$this->table} WHERE  lesson_id = ?  LIMIT 1";
        $result = $this->getConnection()->fetchAll($sql, array(
            $lesson_id
        )) ? : null;
        
        return $result;
    }
    protected function _createSearchQueryBuilder($conditions) {
        $builder = $this->createDynamicQueryBuilder($conditions)->from($this->table, 'homework_member')->andWhere('homework_id = :homework_id')->andWhere('user_id = :user_id')->andWhere('lesson_id = :lesson_id');
        
        return $builder;
    }
    public function getHomeworkTeacherByHomeworkMemberId($homework_member_id)
    {
         $sql = "SELECT * FROM {$this->table} WHERE homework_member_id = ? LIMIT 1";
        
        return $this->getConnection()->fetchAssoc($sql, array(
            $homework_member_id
        ));
    }
    public function delete($id){      
        
        return $this->getConnection()->delete($this->table, array('id' => $id));;
    }
}
