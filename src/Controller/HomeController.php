<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategoryRepository $cr): Response
    {
        // $categories = $cr->findBy([], ["name" => "ASC"]);

        // trier les catégories selon le nombre de topics décroissant
        $categories = $cr->allCategories();
        return $this->render('home/index.html.twig', [
            "categories" => $categories
        ]);
    }
}
