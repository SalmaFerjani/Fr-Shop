<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests unitaires pour le service ProductService
 * 
 * Ces tests vérifient la logique métier du service ProductService,
 * notamment les méthodes de recherche, filtrage et gestion des produits.
 */
class ProductServiceTest extends TestCase
{
    private ProductService $productService;
    private MockObject|ProductRepository $productRepository;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->productService = new ProductService($this->productRepository);
    }

    /**
     * Test de la recherche de produits par nom
     */
    public function testSearchProductsByName(): void
    {
        $searchTerm = 'laptop';
        $expectedProducts = $this->createMockProducts();

        $this->productRepository
            ->expects($this->once())
            ->method('findByNameContaining')
            ->with($searchTerm)
            ->willReturn($expectedProducts);

        $result = $this->productService->searchProducts($searchTerm);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test de la recherche avec un terme vide
     */
    public function testSearchProductsWithEmptyTerm(): void
    {
        $this->productRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($this->createMockProducts());

        $result = $this->productService->searchProducts('');

        $this->assertIsArray($result);
    }

    /**
     * Test du filtrage par catégorie
     */
    public function testFilterProductsByCategory(): void
    {
        $categoryId = 1;
        $expectedProducts = $this->createMockProducts();

        $this->productRepository
            ->expects($this->once())
            ->method('findByCategory')
            ->with($categoryId)
            ->willReturn($expectedProducts);

        $result = $this->productService->getProductsByCategory($categoryId);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test de la récupération des produits en vedette
     */
    public function testGetFeaturedProducts(): void
    {
        $expectedProducts = $this->createMockProducts();

        $this->productRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['isFeatured' => true, 'isActive' => true])
            ->willReturn($expectedProducts);

        $result = $this->productService->getFeaturedProducts();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test de la récupération des produits en stock
     */
    public function testGetAvailableProducts(): void
    {
        $expectedProducts = $this->createMockProducts();

        $this->productRepository
            ->expects($this->once())
            ->method('findAvailableProducts')
            ->willReturn($expectedProducts);

        $result = $this->productService->getAvailableProducts();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test du calcul du prix total d'une liste de produits
     */
    public function testCalculateTotalPrice(): void
    {
        $products = $this->createMockProducts();
        $quantities = [1, 2]; // Quantités pour chaque produit

        $total = $this->productService->calculateTotalPrice($products, $quantities);

        // Produit 1: 100.0 * 1 = 100.0
        // Produit 2: 200.0 * 2 = 400.0
        // Total: 500.0
        $this->assertEquals(500.0, $total);
    }

    /**
     * Test du calcul du prix total avec des quantités différentes
     */
    public function testCalculateTotalPriceWithDifferentQuantities(): void
    {
        $products = $this->createMockProducts();
        $quantities = [3, 1]; // Quantités pour chaque produit

        $total = $this->productService->calculateTotalPrice($products, $quantities);

        // Produit 1: 100.0 * 3 = 300.0
        // Produit 2: 200.0 * 1 = 200.0
        // Total: 500.0
        $this->assertEquals(500.0, $total);
    }

    /**
     * Test de la validation de la disponibilité des produits
     */
    public function testValidateProductAvailability(): void
    {
        $products = $this->createMockProducts();
        $quantities = [1, 5]; // La quantité 5 dépasse le stock du produit 2

        $result = $this->productService->validateProductAvailability($products, $quantities);

        $this->assertFalse($result);
    }

    /**
     * Test de la validation avec des quantités valides
     */
    public function testValidateProductAvailabilityWithValidQuantities(): void
    {
        $products = $this->createMockProducts();
        $quantities = [1, 2]; // Quantités dans les limites du stock

        $result = $this->productService->validateProductAvailability($products, $quantities);

        $this->assertTrue($result);
    }

    /**
     * Test de la mise à jour du stock après commande
     */
    public function testUpdateStockAfterOrder(): void
    {
        $products = $this->createMockProducts();
        $quantities = [1, 2];

        $this->productService->updateStockAfterOrder($products, $quantities);

        // Vérification que le stock a été mis à jour
        $this->assertEquals(9, $products[0]->getStock()); // 10 - 1
        $this->assertEquals(3, $products[1]->getStock()); // 5 - 2
    }

    /**
     * Test de la récupération des produits par plage de prix
     */
    public function testGetProductsByPriceRange(): void
    {
        $minPrice = 50.0;
        $maxPrice = 150.0;
        $expectedProducts = $this->createMockProducts();

        $this->productRepository
            ->expects($this->once())
            ->method('findByPriceRange')
            ->with($minPrice, $maxPrice)
            ->willReturn($expectedProducts);

        $result = $this->productService->getProductsByPriceRange($minPrice, $maxPrice);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test de la récupération des statistiques des produits
     */
    public function testGetProductStatistics(): void
    {
        $this->productRepository
            ->expects($this->exactly(4))
            ->method('count')
            ->willReturnOnConsecutiveCalls(10, 8, 2, 5); // total, active, featured, low stock

        $stats = $this->productService->getProductStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('active', $stats);
        $this->assertArrayHasKey('featured', $stats);
        $this->assertArrayHasKey('low_stock', $stats);
        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(8, $stats['active']);
        $this->assertEquals(2, $stats['featured']);
        $this->assertEquals(5, $stats['low_stock']);
    }

    /**
     * Crée des produits mock pour les tests
     */
    private function createMockProducts(): array
    {
        $category = new Category();
        $category->setName('Test Category');

        $product1 = new Product();
        $product1->setName('Laptop Gaming');
        $product1->setPrice(100.0);
        $product1->setStock(10);
        $product1->setCategory($category);

        $product2 = new Product();
        $product2->setName('Laptop Pro');
        $product2->setPrice(200.0);
        $product2->setStock(5);
        $product2->setCategory($category);

        return [$product1, $product2];
    }
}
