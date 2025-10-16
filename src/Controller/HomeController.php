<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $featuredProducts = $productRepository->findFeaturedProducts();
        $categories = $categoryRepository->findActiveCategories();
        $latestProducts = $productRepository->findActiveProducts();

        return $this->render('home/index.html.twig', [
            'featured_products' => $featuredProducts,
            'categories' => $categories,
            'latest_products' => array_slice($latestProducts, 0, 8), // Limiter à 8 produits
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }
} 