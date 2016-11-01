<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class PageController extends Controller{
    /**
     * @Route("/", name="index")
     */
    public function indexFunction(){
        return new Response("<h1>Welcome</h1>");
    }


    /**
     * @Route("/{slug}")
     */
    public function pageFunction($slug){
        $post = $this->getDoctrine()->getRepository("AppBundle:Post")->findOneBySlug($slug);
        return $this->render("page/page.html.twig", array(
            'post' => $post
        ));
    }
}