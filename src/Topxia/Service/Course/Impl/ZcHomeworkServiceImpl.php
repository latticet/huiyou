<?php
/**
 * 
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-04 15:59:23
 * @version $Id$
 */

namespace Topxia\Service\Course\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ZcHomeworkService;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseService implements ZcHomeworkService
{

	public function uploadHomework($Homework)
	{
		$argument = $Homework;
		if (!ArrayToolkit::requireds($Homework, array('courseId', 'fileId'))) {
			throw $this->createServiceException('参数缺失，上传失败！');
		}

		$course = $this->getCourseService()->getCourse($Homework['courseId']);
		if (empty($course)) {
			throw $this->createServiceException('课程不存在，上传资料失败！');
		}

        $fields = array(
            'courseId' => $Homework['courseId'],
            'lessonId' => empty($Homework['lessonId']) ? 0 : $Homework['lessonId'],
            'description'  => empty($Homework['description']) ? '' : $Homework['description'],
            'userId' => $this->getCurrentUser()->id,
            'createdTime' => time(),
        );

        if (empty($Homework['fileId'])) {
            if (empty($Homework['link'])) {
                throw $this->createServiceException('资料链接地址不能为空，添加资料失败！');
            }
            $fields['fileId'] = 0;
            $fields['link'] = $Homework['link'];
            $fields['title'] = empty($Homework['description']) ? $Homework['link'] : $Homework['description'];
        } else {
            $fields['fileId'] = (int) $Homework['fileId'];
    		$file = $this->getUploadFileService()->getFile($Homework['fileId']);
    		if (empty($file)) {
    			throw $this->createServiceException('文件不存在，上传资料失败！');
    		}
            $fields['link'] = '';
            $fields['title'] = $file['filename'];
            $fields['fileSize'] = $file['size'];
        }
        if(array_key_exists('copyId', $Homework)){
        	$fields['copyId'] = $Homework['copyId'];
        }

		$Homework =  $this->getHomeworkDao()->addHomework($fields);
		// Increase the linked file usage count, if there's a linked file used by this Homework.
		if(!empty($Homework['fileId'])){
			$this->getUploadFileService()->waveUploadFile($Homework['fileId'],'usedCount',1);
		}

		$this->getCourseService()->increaseLessonHomeworkCount($fields['lessonId']);

		$this->dispatchEvent("Homework.create",array('argument'=>$argument,'Homework'=>$Homework));

		return $Homework;
	}

	public function deleteHomework($courseId, $HomeworkId)
	{
		$Homework = $this->getHomeworkDao()->getHomework($HomeworkId);
		if (empty($Homework) || $Homework['courseId'] != $courseId) {
			throw $this->createNotFoundException('课程资料不存在，删除失败。');
		}
		$this->getHomeworkDao()->deleteHomework($HomeworkId);
		// Decrease the linked file usage count, if there's a linked file used by this Homework.
		if(!empty($Homework['fileId'])){
			$this->getUploadFileService()->waveUploadFile($Homework['fileId'],'usedCount',-1);
		}

		if($Homework['lessonId']){
		   $count = $this->getHomeworkDao()->getLessonHomeworkCount($courseId,$Homework['lessonId']);
		   $this->getCourseService()->resetLessonHomeworkCount($Homework['lessonId'], $count);
		}

		$this->dispatchEvent("Homework.delete",$Homework);
	}


	public function findHomeworksByCopyIdAndLockedCourseIds($copyId, $courseIds)
	{
		return $this->getHomeworkDao()->findHomeworksByCopyIdAndLockedCourseIds($copyId, $courseIds);
	}

	public function deleteHomeworkByHomeworkId($HomeworkId)
	{
		return $this->getHomeworkDao()->deleteHomework($HomeworkId);
	}

	public function deleteHomeworksByLessonId($lessonId)
	{
		$Homeworks = $this->getHomeworkDao()->findHomeworksByLessonId($lessonId, 0, 1000);

		$fileIds = ArrayToolkit::column($Homeworks, "fileId");

		// Decrease the linked matrial file usage count, if there are linked Homeworks used by this lesson.
		if(!empty($fileIds)){
			foreach ($fileIds as $fileId) {
				$this->getUploadFileService()->waveUploadFile($fileId,'usedCount',-1);
			}
		}

		return $this->getHomeworkDao()->deleteHomeworksByLessonId($lessonId);
	}

	public function deleteHomeworksByCourseId($courseId)
	{
		$Homeworks = $this->getHomeworkDao()->findHomeworksByCourseId($courseId, 0, 1000);

		$fileIds = ArrayToolkit::column($Homeworks, "fileId");

		// Decrease the linked Homework file usage count, if there are linked Homeworks used by this course.
		if(!empty($fileIds)){
			foreach ($fileIds as $fileId) {
				$this->getUploadFileService()->waveUploadFile($fileId,'usedCount',-1);
			}
		}

		return $this->getHomeworkDao()->deleteHomeworksByCourseId($courseId);
	}

	public function getHomework($courseId, $HomeworkId)
	{
		$Homework = $this->getHomeworkDao()->getHomework($HomeworkId);
		if (empty($Homework) || $Homework['courseId'] != $courseId) {
			return null;
		}
		return $Homework;
	}

	public function findCourseHomeworks($courseId, $start, $limit)
	{
		return $this->getHomeworkDao()->findHomeworksByCourseId($courseId, $start, $limit);
	}

	public function getHomeworkCountByFileId($fileId)
	{
		return $this->getHomeworkDao()->getHomeworkCountByFileId($fileId);
	}

    public function findLessonHomeworks($lessonId, $start, $limit)
    {
        return $this->getHomeworkDao()->findHomeworksByLessonId($lessonId, $start, $limit);
    }

	public function getHomeworkCount($courseId)
	{
		return $this->getHomeworkDao()->getHomeworkCountByCourseId($courseId);
	}


    protected function getHomeworkDao()
    {
    	return $this->createDao('Course.CourseHomeworkDao');
    }

    protected function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }

    protected function getFileService()
    {
    	return $this->createService('Content.FileService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}