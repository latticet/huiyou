<?php
namespace Homework\Service\Homework\Impl;
use Topxia\Service\Common\BaseService;
use Homework\Service\Homework\HomeworkService;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseService implements HomeworkService {
    protected function getHomeworkDao() {
        
        return $this->createDao('Homework:Homework.HomeworkDao');
    }
    public function findHomeworksByCourseIdAndLessonIds($courseId, $lessonIds) {
        
        return array(
            'homework'
        );
    }
    public function findExercisesByLessonIds($lessonIds) {
    }
    public function getHomework($id) {
        if (empty($id)) {
            
            return;
        }
        
        return $this->getHomeworkDao()->getHomework($id);
    }
    public function findHomeworksByIds(array $ids) {
        
        return ArrayToolkit::index($this->getHomeworkDao()->findHomeworksByIds($ids) , 'id');
    }
    public function findAllHomeworks() {
        
        return $this->getHomeworkDao()->findAllHomeworks();
    }
    public function createHomework(array $Homework) {
        if (!ArrayToolkit::requireds($Homework, array(
            'content',
            'course_id',
            'lesson_id'
        ))) {
            throw $this->createServiceException("缺少必要参数，，添加作业失败");
        }
        $this->_filterHomeworkFields($Homework);
        $Homework['create_at'] = time();
        $Homework['update_at'] = $Homework['create_at'];
        $Homework = $this->getHomeworkDao()->addHomework($Homework);
        $this->getLogService()->info('Homework', 'create', "添加作业 {$Homework['content']}(#{$Homework['id']})", $Homework);
        
        return $Homework;
    }
    public function updateHomework($id, array $fields) {
        $Homework = $this->getHomework($id);
        if (empty($Homework)) {
            throw $this->createNoteFoundException("作业(#{$id})不存在，更新作业失败！");
        }
        $fields = ArrayToolkit::parts($fields, array(
            'id',
            'content'
        ));
        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新作业失败！');
        }
        $this->_filterHomeworkFields($fields);
        $fields['update_at'] = $_SERVER['REQUEST_TIME'];
        $this->getLogService()->info('Homework', 'update', "编辑作业 {$fields['content']}(#{$id})", $fields);
        
        return $this->getHomeworkDao()->updateHomework($id, $fields);
    }
    public function deleteHomework($id) {
        $Homework = $this->getHomework($id);
        if (empty($Homework)) {
            throw $this->createNotFoundException();
        }
        $ids = $this->findHomeworkChildrenIds($id);
        $ids[] = $id;
        
        foreach ($ids as $id) {
            $this->getHomeworkDao()->deleteHomework($id);
        }
        $this->getLogService()->info('Homework', 'delete', "删除作业{$Homework['name']}(#{$id})");
    }
    protected function _filterHomeworkFields($fields) {
        
        return $fields;
    }
    protected function getLogService() {
        
        return $this->createService('System.LogService');
    }
    public function searchHomeworks(array $conditions, $orderBy, $start, $limit) {
        
        return $this->getHomeworkDao()->searchHomeworks($conditions, $orderBy, $start, $limit);
    }
    public function searchHomeworksCount(array $conditions) {
        
        return $this->getHomeworkDao()->searchHomeworksCount($conditions);
    }
    public function findHomeworkByLessonId($lessonId) {
        $homework = $this->getHomeworkDao()->findHomeworkByLessonId($lessonId);
        
        return $homework;
    }
    public function getHomeworkByLessonId($lessonId) {
    }
    public function searchResults() {
        
        return array();
    }
    public function uploadMaterial($material) {
        $argument = $material;
        if (!ArrayToolkit::requireds($material, array(
            'courseId',
            'fileId'
        ))) {
            throw $this->createServiceException('参数缺失，上传失败！');
        }
        $course = $this->getCourseService()->getCourse($material['courseId']);
        if (empty($course)) {
            throw $this->createServiceException('课程不存在，上传资料失败！');
        }
        $fields = array(
            'courseId' => $material['courseId'],
            'lessonId' => empty($material['lessonId']) ? 0 : $material['lessonId'],
            'description' => empty($material['description']) ? '' : $material['description'],
            'userId' => $this->getCurrentUser()->id,
            'createdTime' => time() ,
        );
        if (empty($material['fileId'])) {
            if (empty($material['link'])) {
                throw $this->createServiceException('资料链接地址不能为空，添加资料失败！');
            }
            $fields['fileId'] = 0;
            $fields['link'] = $material['link'];
            $fields['title'] = empty($material['description']) ? $material['link'] : $material['description'];
        } else {
            $fields['fileId'] = (int)$material['fileId'];
            $file = $this->getUploadFileService()->getFile($material['fileId']);
            if (empty($file)) {
                throw $this->createServiceException('文件不存在，上传资料失败！');
            }
            $fields['link'] = '';
            $fields['title'] = $file['filename'];
            $fields['fileSize'] = $file['size'];
        }
        if (array_key_exists('copyId', $material)) {
            $fields['copyId'] = $material['copyId'];
        }
        $material = $this->getMaterialDao()->addMaterial($fields);
        // Increase the linked file usage count, if there's a linked file used by this material.
        if (!empty($material['fileId'])) {
            $this->getUploadFileService()->waveUploadFile($material['fileId'], 'usedCount', 1);
        }
        $this->getCourseService()->increaseLessonMaterialCount($fields['lessonId']);
        $this->dispatchEvent("material.create", array(
            'argument' => $argument,
            'material' => $material
        ));
        
        return $material;
    }
        public function delete($id){
          return  $this->getHomeworkDao()->delete($id);
        }
}
