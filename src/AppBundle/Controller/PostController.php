<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Post;
use AppBundle\Form\PostType;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * @Route("/", name="post_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $posts = $em->getRepository('AppBundle:Post')->findAll();

        return $this->render('post/index.html.twig', array(
            'posts' => $posts,
        ));
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="post_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm('AppBundle\Form\PostType', $post);
        $form->handleRequest($request);

        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $title = $post->getTitle();
            $content = $post->getContent();
            $slug = preg_replace( '/\s+/', '-', $title );
            $post->setSlug($slug);

            // error checking
            if(preg_match('/([^0-9A-Za-z ])/', $title)){
                $error = "invalid character in title";
            }else if($content == null){
                $error = "empty content";
            }else if($title == null){
                $error = "empty title";
            }else{
                $posts = $this->getDoctrine()->getRepository("AppBundle:Post")->findAll();
                foreach ($posts as $p){
                    if ($p->getSlug() == $slug){
                        $error = "repeated title";
                        break;
                    }
                }
            }


            if ($error){
                return $this->render('post/new.html.twig', array(
                    'post' => $post,
                    'form' => $form->createView(),
                    'error' => $error
                ));
            }else{
                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();
                return $this->redirectToRoute('post_show', array('slug' => $post->getSlug()));
            }

        }

        return $this->render('post/new.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
            'error' => $error
        ));
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{slug}/show", name="post_show")
     * @Method("GET")
     */
    public function showAction($slug)
    {
        $post = $this->getDoctrine()->getRepository("AppBundle:Post")->findOneBySlug($slug);
        $deleteForm = $this->createDeleteForm($post);

        return $this->render('post/show.html.twig', array(
            'post' => $post,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{slug}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, $slug)
    {
        $post = $this->getDoctrine()->getRepository("AppBundle:Post")->findOneBySlug($slug);
        $deleteForm = $this->createDeleteForm($post);
        $editForm = $this->createForm('AppBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('post_edit', array('slug' => $post->getSlug()));
        }

        return $this->render('post/edit.html.twig', array(
            'post' => $post,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}", name="post_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Post $post)
    {
        $form = $this->createDeleteForm($post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }

        return $this->redirectToRoute('post_index');
    }

    /**
     * Creates a form to delete a Post entity.
     *
     * @param Post $post The Post entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Post $post)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('post_delete', array('id' => $post->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
