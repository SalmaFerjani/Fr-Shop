<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Category
 * 
 * Ces tests vérifient le comportement des méthodes métier de l'entité Category,
 * notamment la gestion des produits associés et les propriétés de base.
 */
class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    /**
     * Test de création d'une catégorie avec des valeurs par défaut
     */
    public function testCategoryCreationWithDefaults(): void
    {
        $this->assertTrue($this->category->isIsActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->category->getUpdatedAt());
    }

    /**
     * Test des getters et setters basiques
     */
    public function testBasicGettersAndSetters(): void
    {
        // Test du nom
        $this->category->setName('Électronique');
        $this->assertEquals('Électronique', $this->category->getName());

        // Test de la description
        $this->category->setDescription('Catégorie pour tous les produits électroniques');
        $this->assertEquals('Catégorie pour tous les produits électroniques', $this->category->getDescription());

        // Test de l'image
        $this->category->setImage('electronics.jpg');
        $this->assertEquals('electronics.jpg', $this->category->getImage());
    }

    /**
     * Test de la propriété isActive
     */
    public function testIsActiveProperty(): void
    {
        $this->category->setIsActive(false);
        $this->assertFalse($this->category->isIsActive());

        $this->category->setIsActive(true);
        $this->assertTrue($this->category->isIsActive());
    }

    /**
     * Test de l'association avec des produits
     */
    public function testProductsAssociation(): void
    {
        $product1 = new Product();
        $product1->setName('Produit 1');
        $product1->setPrice(29.99);

        $product2 = new Product();
        $product2->setName('Produit 2');
        $product2->setPrice(49.99);

        // Ajout des produits à la catégorie
        $this->category->addProduct($product1);
        $this->category->addProduct($product2);

        $products = $this->category->getProducts();
        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($product1));
        $this->assertTrue($products->contains($product2));

        // Vérification que les produits sont bien associés à cette catégorie
        $this->assertSame($this->category, $product1->getCategory());
        $this->assertSame($this->category, $product2->getCategory());
    }

    /**
     * Test de la suppression d'un produit
     */
    public function testRemoveProduct(): void
    {
        $product = new Product();
        $product->setName('Produit à supprimer');
        $product->setPrice(19.99);

        $this->category->addProduct($product);
        $this->assertTrue($this->category->getProducts()->contains($product));

        $this->category->removeProduct($product);
        $this->assertFalse($this->category->getProducts()->contains($product));
        $this->assertNull($product->getCategory());
    }

    /**
     * Test de l'ajout d'un produit déjà présent (ne doit pas créer de doublon)
     */
    public function testAddDuplicateProduct(): void
    {
        $product = new Product();
        $product->setName('Produit unique');
        $product->setPrice(15.99);

        $this->category->addProduct($product);
        $this->category->addProduct($product); // Ajout du même produit

        $this->assertCount(1, $this->category->getProducts());
    }

    /**
     * Test de la suppression d'un produit non associé
     */
    public function testRemoveNonAssociatedProduct(): void
    {
        $product = new Product();
        $product->setName('Produit non associé');
        $product->setPrice(25.99);

        // Tentative de suppression d'un produit non associé
        $this->category->removeProduct($product);
        
        // Ne doit pas générer d'erreur et la collection doit rester vide
        $this->assertCount(0, $this->category->getProducts());
    }

    /**
     * Test de la gestion des dates
     */
    public function testDateManagement(): void
    {
        $newDate = new \DateTimeImmutable('2024-01-01');
        
        $this->category->setCreatedAt($newDate);
        $this->assertEquals($newDate, $this->category->getCreatedAt());
        
        $this->category->setUpdatedAt($newDate);
        $this->assertEquals($newDate, $this->category->getUpdatedAt());
    }

    /**
     * Test du chaînage des méthodes (fluent interface)
     */
    public function testMethodChaining(): void
    {
        $result = $this->category
            ->setName('Vêtements')
            ->setDescription('Catégorie vêtements')
            ->setIsActive(true);

        $this->assertSame($this->category, $result);
        $this->assertEquals('Vêtements', $this->category->getName());
        $this->assertEquals('Catégorie vêtements', $this->category->getDescription());
        $this->assertTrue($this->category->isIsActive());
    }

    /**
     * Test avec plusieurs produits dans la catégorie
     */
    public function testMultipleProductsInCategory(): void
    {
        $products = [];
        for ($i = 1; $i <= 5; $i++) {
            $product = new Product();
            $product->setName("Produit $i");
            $product->setPrice(10.0 * $i);
            $products[] = $product;
            $this->category->addProduct($product);
        }

        $this->assertCount(5, $this->category->getProducts());
        
        foreach ($products as $product) {
            $this->assertTrue($this->category->getProducts()->contains($product));
            $this->assertSame($this->category, $product->getCategory());
        }
    }

    /**
     * Test de la suppression de tous les produits
     */
    public function testRemoveAllProducts(): void
    {
        // Ajout de plusieurs produits
        for ($i = 1; $i <= 3; $i++) {
            $product = new Product();
            $product->setName("Produit $i");
            $product->setPrice(20.0 * $i);
            $this->category->addProduct($product);
        }

        $this->assertCount(3, $this->category->getProducts());

        // Suppression de tous les produits
        foreach ($this->category->getProducts() as $product) {
            $this->category->removeProduct($product);
        }

        $this->assertCount(0, $this->category->getProducts());
    }

    /**
     * Test de la catégorie avec des valeurs nulles
     */
    public function testCategoryWithNullValues(): void
    {
        $this->category
            ->setName('Catégorie Test')
            ->setDescription(null)
            ->setImage(null);

        $this->assertEquals('Catégorie Test', $this->category->getName());
        $this->assertNull($this->category->getDescription());
        $this->assertNull($this->category->getImage());
    }
}
