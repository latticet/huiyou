<?php
/**
 * 班级接口
 * @authors 赵昌 (80330582@163.com)
 * @date    2015-11-09 10:44:09
 * @version $Id$
 */
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Topxia\Common\ArrayToolkit;
$api = $app['controllers_factory'];
//班级列表
$api->get('/', function (Request $request) {
	$start = $request->query->get('start', 0);
	$limit = $request->query->get('limit', 5);
	$conditions = array();
	$orderBy = array(
		'createdTime',
		'DESC'
	);
	$classrooms = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->searchClassrooms($conditions, $orderBy, $start, $limit);
	$count = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->searchClassroomsCount($conditions);
	$data = array();
	$data['classroom_list'] = filters($classrooms, 'classroom');
	$data['count'] = $count;
	
	return $data;
});
//我的班级列表
$api->get('/user/{user_id}', function (Request $request, $user_id) {
	$start = $request->query->get('start', 0);
	$limit = $request->query->get('limit', 5);
	$conditions = array();
	$orderBy = array(
		'createdTime',
		'DESC'
	);
	$classrooms = array();
	$studentClassrooms = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->searchMembers(array(
		'role' => 'student',
		'userId' => $user_id
	) , $orderBy, $start, $limit);
	$auditorClassrooms = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->searchMembers(array(
		'role' => 'auditor',
		'userId' => $user_id
	) , $orderBy, $start, $limit);
	$classrooms = array_merge($studentClassrooms, $auditorClassrooms);
	$classroomIds = ArrayToolkit::column($classrooms, 'classroomId');
	$conditions = array(
		'status' => 'published',
		'showable' => '1',
		'classroomIds' => $classroomIds
	);
	$classrooms = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->searchClassrooms($conditions, $orderBy, $start, $limit);
	$count = ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService')->searchClassroomsCount($conditions);
	$data = array();
	$data['classroom_list'] = filters($classrooms, 'classroom');
	$data['count'] = $count;
	
	return $data;
});
//班级详情
$api->get('/{id}', function ($id) {
	$classroom = convert($id, 'classroom');
	
	return filter($classroom, 'classroom');
});

return $api;
