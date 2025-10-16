<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/products')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->get('category');
        $search = $request->query->get('search');

        if ($search) {
            $products = $productRepository->searchProducts($search);
        } elseif ($categoryId) {
            $products = $productRepository->findByCategory($categoryId);
        } else {
            $products = $productRepository->findActiveProducts();
        }

        $categories = $categoryRepository->findActiveCategories();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'selected_category' => $categoryId,
            'search' => $search,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'])]
    public function show(int $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }
        
        if (!$product->isIsActive()) {
            throw $this->createNotFoundException('Ce produit n\'est pas disponible.');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/category/{id}', name: 'app_products_by_category', requirements: ['id' => '\d+'])]
    public function byCategory(int $id, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category || !$category->isIsActive()) {
            throw $this->createNotFoundException('Catégorie non trouvée.');
        }

        $products = $productRepository->findByCategory($id);
        $categories = $categoryRepository->findActiveCategories();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'selected_category' => $id,
            'current_category' => $category,
            'search' => null, // Pas de recherche dans cette vue
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Récupérer la liste des produits",
     *     description="Récupère tous les produits actifs avec possibilité de filtrage par catégorie et recherche",
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="ID de la catégorie pour filtrer les produits",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Terme de recherche pour filtrer les produits",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des produits récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="image", type="string"),
     *                 @OA\Property(property="category", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    #[Route('/api', name: 'api_products', methods: ['GET'])]
    public function apiIndex(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $search = $request->query->get('search');

        if ($search) {
            $products = $productRepository->searchProducts($search);
        } elseif ($categoryId) {
            $products = $productRepository->findByCategory($categoryId);
        } else {
            $products = $productRepository->findActiveProducts();
        }

        $productsData = [];
        foreach ($products as $product) {
            $productsData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'image' => $product->getImage(),
                'category' => [
                    'id' => $product->getCategory()->getId(),
                    'name' => $product->getCategory()->getName()
                ]
            ];
        }

        return new JsonResponse($productsData);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Récupérer un produit par son ID",
     *     description="Récupère les détails d'un produit spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du produit",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produit récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="image", type="string"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Produit non trouvé"
     *     )
     * )
     */
    #[Route('/api/{id}', name: 'api_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function apiShow(int $id, ProductRepository $productRepository): JsonResponse
    {
        $product = $productRepository->find($id);
        
        if (!$product) {
            return new JsonResponse(['error' => 'Produit non trouvé.'], 404);
        }
        
        if (!$product->isIsActive()) {
            return new JsonResponse(['error' => 'Ce produit n\'est pas disponible.'], 404);
        }

        $productData = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'image' => $product->getImage(),
            'category' => [
                'id' => $product->getCategory()->getId(),
                'name' => $product->getCategory()->getName()
            ]
        ];

        return new JsonResponse($productData);
    }
} 