<?php

namespace Homework\HomeworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('HomeworkBundle:Default:index.html.twig', array('name' => $name));
    }
    public function listAction($courseId,$status){
    	$name='哈哈';
    	return $this->render('HomeworkBundle:Default:index.html.twig', array('name' => $name));

    }
}
