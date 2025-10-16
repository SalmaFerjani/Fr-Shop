<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\OrderItem;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Product
 * 
 * Ces tests vérifient le comportement des méthodes métier de l'entité Product,
 * notamment les calculs de prix, la gestion du stock, et les opérations sur les images.
 */
class ProductTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        $this->product = new Product();
    }

    /**
     * Test de création d'un produit avec des valeurs par défaut
     */
    public function testProductCreationWithDefaults(): void
    {
        $this->assertTrue($this->product->isIsActive());
        $this->assertFalse($this->product->isIsFeatured());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->product->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->product->getUpdatedAt());
        $this->assertIsArray($this->product->getImages());
        $this->assertEmpty($this->product->getImages());
    }

    /**
     * Test des getters et setters basiques
     */
    public function testBasicGettersAndSetters(): void
    {
        // Test du nom
        $this->product->setName('Produit Test');
        $this->assertEquals('Produit Test', $this->product->getName());

        // Test de la description
        $this->product->setDescription('Description du produit test');
        $this->assertEquals('Description du produit test', $this->product->getDescription());

        // Test du prix
        $this->product->setPrice(29.99);
        $this->assertEquals(29.99, $this->product->getPrice());

        // Test du stock
        $this->product->setStock(50);
        $this->assertEquals(50, $this->product->getStock());

        // Test du SKU
        $this->product->setSku('SKU-001');
        $this->assertEquals('SKU-001', $this->product->getSku());
    }

    /**
     * Test du calcul du prix avec TVA (20%)
     */
    public function testPriceWithTva(): void
    {
        $this->product->setPrice(100.0);
        $expectedPriceWithTva = 120.0; // 100 * 1.20
        
        $this->assertEquals($expectedPriceWithTva, $this->product->getPriceWithTva());
    }

    /**
     * Test du calcul du prix avec TVA avec des valeurs décimales
     */
    public function testPriceWithTvaDecimal(): void
    {
        $this->product->setPrice(25.50);
        $expectedPriceWithTva = 30.60; // 25.50 * 1.20
        
        $this->assertEquals($expectedPriceWithTva, $this->product->getPriceWithTva());
    }

    /**
     * Test de la vérification du stock
     */
    public function testIsInStock(): void
    {
        // Test avec stock positif
        $this->product->setStock(10);
        $this->assertTrue($this->product->isInStock());

        // Test avec stock zéro
        $this->product->setStock(0);
        $this->assertFalse($this->product->isInStock());

        // Test avec stock négatif
        $this->product->setStock(-5);
        $this->assertFalse($this->product->isInStock());
    }

    /**
     * Test de la gestion des images
     */
    public function testImageManagement(): void
    {
        // Test de l'ajout d'images
        $this->product->addImage('image1.jpg');
        $this->product->addImage('image2.jpg');
        
        $images = $this->product->getImages();
        $this->assertCount(2, $images);
        $this->assertContains('image1.jpg', $images);
        $this->assertContains('image2.jpg', $images);

        // Test de l'ajout d'une image déjà présente (ne doit pas être dupliquée)
        $this->product->addImage('image1.jpg');
        $this->assertCount(2, $this->product->getImages());

        // Test de la suppression d'une image
        $this->product->removeImage('image1.jpg');
        $images = $this->product->getImages();
        $this->assertCount(1, $images);
        $this->assertNotContains('image1.jpg', $images);
        $this->assertContains('image2.jpg', $images);

        // Test de la suppression d'une image inexistante
        $this->product->removeImage('image_inexistante.jpg');
        $this->assertCount(1, $this->product->getImages());
    }

    /**
     * Test de la gestion des images via setImages
     */
    public function testSetImages(): void
    {
        $images = ['img1.jpg', 'img2.jpg', 'img3.jpg'];
        $this->product->setImages($images);
        
        $this->assertEquals($images, $this->product->getImages());
    }

    /**
     * Test de l'image principale
     */
    public function testMainImage(): void
    {
        $this->product->setMainImage('main_image.jpg');
        $this->assertEquals('main_image.jpg', $this->product->getMainImage());
    }

    /**
     * Test des propriétés booléennes
     */
    public function testBooleanProperties(): void
    {
        // Test isActive
        $this->product->setIsActive(false);
        $this->assertFalse($this->product->isIsActive());

        $this->product->setIsActive(true);
        $this->assertTrue($this->product->isIsActive());

        // Test isFeatured
        $this->product->setIsFeatured(true);
        $this->assertTrue($this->product->isIsFeatured());

        $this->product->setIsFeatured(false);
        $this->assertFalse($this->product->isIsFeatured());
    }

    /**
     * Test de l'association avec une catégorie
     */
    public function testCategoryAssociation(): void
    {
        $category = new Category();
        $category->setName('Catégorie Test');
        
        $this->product->setCategory($category);
        $this->assertSame($category, $this->product->getCategory());
    }

    /**
     * Test de l'association avec des OrderItems
     */
    public function testOrderItemsAssociation(): void
    {
        $orderItem = new OrderItem();
        $orderItem->setQuantity(2);
        
        $this->product->addOrderItem($orderItem);
        
        $this->assertTrue($this->product->getOrderItems()->contains($orderItem));
        $this->assertSame($this->product, $orderItem->getProduct());
    }

    /**
     * Test de la suppression d'un OrderItem
     */
    public function testRemoveOrderItem(): void
    {
        $orderItem = new OrderItem();
        $orderItem->setQuantity(1);
        
        $this->product->addOrderItem($orderItem);
        $this->assertTrue($this->product->getOrderItems()->contains($orderItem));
        
        $this->product->removeOrderItem($orderItem);
        $this->assertFalse($this->product->getOrderItems()->contains($orderItem));
        $this->assertNull($orderItem->getProduct());
    }

    /**
     * Test de la gestion des dates
     */
    public function testDateManagement(): void
    {
        $newDate = new \DateTimeImmutable('2024-01-01');
        
        $this->product->setCreatedAt($newDate);
        $this->assertEquals($newDate, $this->product->getCreatedAt());
        
        $this->product->setUpdatedAt($newDate);
        $this->assertEquals($newDate, $this->product->getUpdatedAt());
    }

    /**
     * Test du chaînage des méthodes (fluent interface)
     */
    public function testMethodChaining(): void
    {
        $result = $this->product
            ->setName('Produit Test')
            ->setPrice(19.99)
            ->setStock(25)
            ->setIsFeatured(true);
        
        $this->assertSame($this->product, $result);
        $this->assertEquals('Produit Test', $this->product->getName());
        $this->assertEquals(19.99, $this->product->getPrice());
        $this->assertEquals(25, $this->product->getStock());
        $this->assertTrue($this->product->isIsFeatured());
    }
}
