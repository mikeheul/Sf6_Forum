<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ForumController extends AbstractController
{
    #[Route('/forum/topics/{id}', name: 'topics')]
    public function topics(ManagerRegistry $doctrine, Category $category = null, Request $request, PaginatorInterface $paginator): Response
    {
        if($category) {
            $topic = new Topic();
            $post = new Post();
            $form = $this->createForm(TopicType::class, $topic);
            $form->handleRequest($request);
            
            $em = $doctrine->getManager(); 
            
            if ($form->isSubmitted() && $form->isValid()) {
                $topic = $form->getData();
                $topic->setUser($this->getUser());
                $category->addTopic($topic);
                $em->persist($topic);
    
                $post->setText($form->get('first_message')->getData());
                $post->setUser($this->getUser());
                $post->setTopic($topic);
                $em->persist($post);
    
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $category->getId()]);
            }
    
            $topics = $paginator->paginate($category->getTopics(), $request->query->getInt("page", 1), 5);
            return $this->render('forum/topics.html.twig', [
                "category" => $category,
                "topics" => $topics,
                "formAddTopic" => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/forum/posts/{id}', name: 'posts')]
    public function posts(ManagerRegistry $doctrine, Topic $topic = null, Request $request, PaginatorInterface $paginator): Response
    {
        if($topic) {
            $post = new Post();
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);
            
            $em = $doctrine->getManager(); 
            
            if ($form->isSubmitted() && $form->isValid()) {
                $post = $form->getData();
                $post->setUser($this->getUser());
                $topic->addPost($post);
                $em->persist($post);
                $em->flush();
    
                return $this->redirectToRoute("posts", ["id" => $topic->getId()]);
            }
    
            $posts = $paginator->paginate($topic->getPosts(), $request->query->getInt("page", 1), 4);
            return $this->render('forum/posts.html.twig', [
                "topic" => $topic,
                "posts" => $posts,
                "formAddPost" => $form->createView() 
            ]);
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/forum/topic/lock/{id}', name: 'lock_topic')]
    public function lockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            if($this->getUser()) {
                $topic->setLocked(true);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/forum/topic/unlock/{id}', name: 'unlock_topic')]
    public function unlockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            if($this->getUser()) {
                $topic->setLocked(false);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/forum/topic/solve/{id}', name: 'solve_topic')]
    public function solveTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            if($this->getUser()) {
                $topic->setResolved(true);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/forum/topic/unsolve/{id}', name: 'unsolve_topic')]
    public function unsolveTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            if($this->getUser()) {
                $topic->setResolved(false);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    // test axios lock
    #[Route('/forum/topic/axiosLock/{id}', name: 'axiosLock_topic')]
    public function axioLockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            if($this->getUser()) {
                $topic->setLocked(true);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->json([
                    'code' => 200, 
                    'message' => 'Topic locked successfully !',
                    'locked' => $topic->isLocked()
                ], 200);
                //return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    #[Route('/forum/topic/axiosUnlock/{id}', name: 'axiosUnlock_topic')]
    public function axioUnlockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            if($this->getUser()) {
                $topic->setLocked(false);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->json([
                    'code' => 200, 
                    'message' => 'Topic unlocked successfully !',
                    'locked' => $topic->isLocked()
                ], 200);
                //return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }
}
