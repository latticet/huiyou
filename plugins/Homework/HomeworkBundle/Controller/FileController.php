<?php
namespace Homework\HomeworkBundle\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Util\UploadToken;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Homework\Service\Homework\HomeworkService;

class FileController extends \Topxia\WebBundle\Controller\BaseController {
    public function uploadAction(Request $request) {
      
        $homework_id = $request->request->get('homework_id', 0);
        $course_id = $request->request->get('course_id', 0);
        $lesson_id = $request->request->get('lesson_id', 0);
        $member_type = $request->request->get('member_type', '');
        $remark = $request->request->get('remark', '');
        $pic = $request->request->get('pic', '');
        $groupCode = 'homework';
        $type = 'image';
        $data = array();
        $data['homework_id'] = $homework_id;
        $data['lesson_id'] = $lesson_id;
        //$data['type']=$member_type;
        $data['remark'] = $remark;
        $file = $request->files->get('file');
        if (!empty($file)) {
            if ($type == 'image') {
                if (!FileToolkit::isImageFile($file)) {
                    throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
                }
            } else {
                throw new \RuntimeException("上传类型不正确！");
            }
            $record = $this->getFileService()->uploadFile($groupCode, $file);
            $request->getSession()->set("fileId", $record["id"]);
            $data['user_id'] = $record['userId'];
            $data['pic'] = $record['uri'];
        } else {
            $user = $this->getCurrentUser();
            $data['user_id'] = $user['id'];
            if(!empty($pic)){
                 $data['pic']=$pic;

            }
           
        }
        
        //保存作业记录
        if ($member_type == 'teacher') {
            $data['homework_member_id'] = $request->request->get('homework_member_id', 0);
            $service = $this->getHomeworkTeacherService();
            $check_data = $service->findHomeworkByUserIdAndLessonId($data['user_id'], $lesson_id);
        if (!empty($check_data)) {
            $data['update_at'] = $_SERVER['REQUEST_TIME'];
            
            $info = $service->updateHomeworkTeacher($check_data['id'], $data);
        } else {
            $data['create_at'] = $_SERVER['REQUEST_TIME'];
            $info = $service->createHomeworkTeacher($data);
        }
        } elseif ($member_type == 'student') {
            $service = $this->getHomeworkMemberService();
            $check_data = $service->findHomeworkByUserIdAndLessonId($data['user_id'], $lesson_id);
        if (!empty($check_data)) {
            $data['update_at'] = $_SERVER['REQUEST_TIME'];
            $info = $service->updateHomeworkMember($check_data['id'], $data);
        } else {
            $data['create_at'] = $_SERVER['REQUEST_TIME'];
            $info = $service->createHomeworkMember($data);
        }
        } else {
            die('意外的情况');
        }
      
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
       public function uploadAvatarAction(Request $request)
    {
        list($groupCode, $type) = $this->tryUploadFile($request);

        if(!$this->isGroup($groupCode)) {
            
            return $this->createMessageResponse("error", "参数不正确");
        }
        
        $file = $request->files->get('file');

        if ($type == 'image') {
            if (!FileToolkit::isImageFile($file)) {
                throw new \RuntimeException("您上传的不是图片文件，请重新上传。");
            }
        } else {
            throw new \RuntimeException("上传类型不正确！");
        }

        $record = $this->getFileService()->uploadFile($groupCode, $file);
        $record['url'] = $this->get('topxia.twig.web_extension')->getFilePath($record['uri']);

        $request->getSession()->set("fileId", $record["id"]);
        return $this->createJsonResponse($record);
    }

    public function cropImgAction(Request $request) {
        $options = $request->request->all();
        
        if (empty($options['group'])) {
            $options['group'] = "default";
        }
        if (!$this->isGroup($options['group'])) {
            
            return $this->createMessageResponse("error", "参数不正确");
        }
        $fileId = $request->getSession()->get("fileId");
        if (empty($fileId)) {
            
            return $this->createMessageResponse("error", "参数不正确");
        }
        $record = $this->getFileService()->getFile($fileId);
        if (empty($record)) {
            
            return $this->createMessageResponse("error", "文件不存在");
        }
        $parsed = $this->getFileService()->parseFileUri($record['uri']);
        $filePaths = FileToolKit::cropImages($parsed["fullpath"], $options);
        $fields = array();
        
        foreach ($filePaths as $key => $value) {
            $file = $this->getFileService()->uploadFile($options["group"], new File($value));
            $fields[] = array(
                "type" => $key,
                "id" => $file['id']
            );
        }
        if (isset($options["deleteOriginFile"]) && $options["deleteOriginFile"] == 0) {
            $fields[] = array(
                "type" => "origin",
                "id" => $record['id']
            );
        } else {
            $this->getFileService()->deleteFileByUri($record["uri"]);
        }
        
        return $this->createJsonResponse($fields);
    }
    protected function isGroup($group) {
        $groups = $this->getFileService()->getAllFileGroups();
        $codes = ArrayToolkit::column($groups, "code");
        
        return in_array($group, $codes);
    }
    protected function tryUploadFile($request) {
        $token = $request->request->get('token');
        $maker = new UploadToken();
        $token = $maker->parse($token);
        // if (empty($token)) {
        //     throw new \RuntimeException("上传授权码已过期，请刷新页面后重试！");
        // }
        $groupCode = $token['group'];
        if (empty($groupCode)) {
            $groupCode = "default";
        }
        
        return array(
            $groupCode,
            $token["type"]
        );
    }
    protected function getFileService() {
        
        return $this->getServiceKernel()->createService('Content.FileService');
    }
    protected function getHomeworkMemberService() {
        
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkMemberService');
    }
    protected function getHomeworkTeacherService() {
        
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkTeacherService');
    }
    protected function getHomeworkService() {
        
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }
}
