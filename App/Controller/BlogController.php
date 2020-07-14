<?php
/**
 * A test class
 *
 * @param foo bar
 * @return baz
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * 模拟在User控制其中定义注解路由
 * @Route("/user/", name="userController")
 */
class BlogController
{
    // This property is used by the marking store
    // 此属性被marking stroe所用
    public $marking;
    public $title;
    public $content;

    /**
     * 匹配 URL: /blog
     * @Route("/blog", name="blog_list")
     */
    public function list()
    {
        die("list");
    }

    public function getMarking()
    {
        return $this->marking;
    }

    public function setMarking($marking)
    {
        return $this->marking = $marking;
    }

    public static function getCurrentState()
    {

    }

    public static function setCurrentState()
    {

    }

    /**
     * 匹配 URL: /blog/*
     * @Route("/blog/{id}", name="blog_show")
     * @param mixed $id
     */
    public function show($id)
    {
        echo "show_______________";
        echo "id:" . $id;
    }

    public function article($id, $title)
    {
        echo "article_______________";
        var_dump($id, $title);
    }
}