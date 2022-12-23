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
    /**
     * Afficher les topics en fontion de la catégorie
     */
    #[Route('/forum/topics/{id}', name: 'topics')]
    public function topics(ManagerRegistry $doctrine, Category $category = null, Request $request, PaginatorInterface $paginator): Response
    {
        // si la catégorie existe
        if($category) {
            // gestion du formulaire pour ajouter un topic et son premier message
            // Dans TopicType, le message est en "mapped = false" pour éviter que Symfony génère une erreur pour non respect des attributs de la classe Topic
            $topic = new Topic();
            $post = new Post();
            $form = $this->createForm(TopicType::class, $topic);
            $form->handleRequest($request);
            
            $em = $doctrine->getManager(); 
            
            // quand le formulaire est soumis et valide (filtres du FormType)
            if ($form->isSubmitted() && $form->isValid()) {
                // on récupère les données du formulaire
                $topic = $form->getData();
                // on associe l'utilisateur connecté au topic
                $topic->setUser($this->getUser());
                // on ajoute le topic dans la catégorie courante
                $category->addTopic($topic);
                $em->persist($topic);
    
                // on récupère le texte du premier message pour hydrater l'objet Post
                $post->setText($form->get('first_message')->getData());
                // on associe l'utilisateur connecté au post
                $post->setUser($this->getUser());
                // on associe le topic au post
                $post->setTopic($topic);
                $em->persist($post);
    
                // on enregistre en BDD
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $category->getId()]);
            }
    
            // pagination avec KnpPaginator -> 5 topic max par page
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

    /**
     * Editer un topic (ne pas afficher le textarea du 1er message dans le cas de l'édition)
     */
    #[Route('/forum/topic/edit/{id}', name: 'topic_edit')]
    public function editTopic(ManagerRegistry $doctrine, Topic $topic, Request $request): Response
    {
        // éditer le topic uniquement si on en est l'auteur
        if($topic->getUser() == $this->getUser()) {
            $form = $this->createForm(TopicType::class, $topic, ['edit' => true]);
            $form->handleRequest($request);
            
            if($form->isSubmitted() && $form->isValid()) {
                $em = $doctrine->getManager();
                $em->flush();
                return $this->redirectToRoute('topics', ['id' => $topic->getCategory()->getId()]);
            }
    
            return $this->render('forum/edit_topic.html.twig', [
                'formEditTopic' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * Afficher les posts en fonction du topic
     */
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

    /**
     * Verrouiller un topic (id)
     */
    #[Route('/forum/topic/lock/{id}', name: 'lock_topic')]
    public function lockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        // si le topic existe
        if($topic) {
            // si l'utilisateur est connecté et s'il est l'auteur du topic
            if($this->getUser() && $topic->getUser() == $this->getUser()) {
                // false -> true
                $topic->setLocked(true);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * Déverrouiller un topic
     */
    #[Route('/forum/topic/unlock/{id}', name: 'unlock_topic')]
    public function unlockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
           // si l'utilisateur est connecté et s'il est l'auteur du topic
            if($this->getUser() && $topic->getUser() == $this->getUser()) {
                $topic->setLocked(false);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * Résoudre un topic
     */
    #[Route('/forum/topic/solve/{id}', name: 'solve_topic')]
    public function solveTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            // si l'utilisateur est connecté et s'il est l'auteur du topic
            if($this->getUser() && $topic->getUser() == $this->getUser()) {
                $topic->setResolved(true);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * Ne pas résoudre un topic
     */
    #[Route('/forum/topic/unsolve/{id}', name: 'unsolve_topic')]
    public function unsolveTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            // si l'utilisateur est connecté et s'il est l'auteur du topic
            if($this->getUser() && $topic->getUser() == $this->getUser()) {
                $topic->setResolved(false);
                $em = $doctrine->getManager();
                $em->flush();
    
                return $this->redirectToRoute("topics", ["id" => $topic->getCategory()->getId()]);
            }
        } else {
            return $this->redirectToRoute("app_home");
        }
    }

    /**
     * Verrouiller un topic en Ajax (pas de refresh de la page)
     */
    #[Route('/forum/topic/axiosLock/{id}', name: 'axiosLock_topic')]
    public function axioLockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            // si l'utilisateur est connecté et s'il est l'auteur du topic
            if($this->getUser() && $topic->getUser() == $this->getUser()) {
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

    /**
     * Déverrouiller un topic en Ajax (pas de refresh de la page)
     */
    #[Route('/forum/topic/axiosUnlock/{id}', name: 'axiosUnlock_topic')]
    public function axioUnlockTopic(ManagerRegistry $doctrine, Topic $topic = null, Request $request): Response
    {
        if($topic) {
            // si l'utilisateur est connecté et s'il est l'auteur du topic
            if($this->getUser() && $topic->getUser() == $this->getUser()) {
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
