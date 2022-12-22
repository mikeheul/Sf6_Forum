<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Afficher toutes les catégories du forum
     */
    #[Route('/', name: 'app_home')]
    public function index(CategoryRepository $cr): Response
    {
        // trier les catégories selon le nombre de topics décroissant (voir méthode dans Repository)
        $categories = $cr->allCategories();
        return $this->render('home/index.html.twig', [
            "categories" => $categories
        ]);
    }
}
