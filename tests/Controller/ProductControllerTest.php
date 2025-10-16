<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests d'intégration pour le contrôleur ProductController
 * 
 * Ces tests vérifient le comportement des endpoints du contrôleur ProductController,
 * notamment les réponses HTTP, les codes de statut et le contenu des réponses.
 */
class ProductControllerTest extends WebTestCase
{
    /**
     * Test de la page d'accueil des produits
     */
    public function testProductIndexPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Produits');
    }

    /**
     * Test de l'affichage d'un produit spécifique
     */
    public function testProductShowPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/1');

        // Le produit peut exister ou non selon les données de test
        $response = $client->getResponse();
        $this->assertTrue(
            $response->isSuccessful() || $response->getStatusCode() === 404,
            'La réponse doit être soit réussie (200) soit une erreur 404'
        );
    }

    /**
     * Test de l'affichage d'un produit inexistant
     */
    public function testProductShowPageNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * Test de la recherche de produits
     */
    public function testProductSearch(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products?search=test');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[method="GET"]');
    }

    /**
     * Test du filtrage par catégorie
     */
    public function testProductFilterByCategory(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products?category=1');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test de la pagination des produits
     */
    public function testProductPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products?page=2');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test de l'API des produits (endpoint JSON)
     */
    public function testProductApiEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    /**
     * Test de l'API des produits avec paramètres de recherche
     */
    public function testProductApiWithSearch(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api?search=laptop');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    /**
     * Test de l'API des produits avec filtrage par catégorie
     */
    public function testProductApiWithCategoryFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api?category=1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    /**
     * Test de l'API pour un produit spécifique
     */
    public function testProductApiShow(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api/1');

        $response = $client->getResponse();
        $this->assertTrue(
            $response->isSuccessful() || $response->getStatusCode() === 404,
            'La réponse doit être soit réussie (200) soit une erreur 404'
        );
        
        if ($response->isSuccessful()) {
            $this->assertResponseHeaderSame('content-type', 'application/json');
            $data = json_decode($response->getContent(), true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('id', $data);
        }
    }

    /**
     * Test de l'API pour un produit inexistant
     */
    public function testProductApiShowNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api/999999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Test de la page d'administration des produits (nécessite authentification)
     */
    public function testProductAdminPageRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/products');

        // Redirection vers la page de connexion
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test de la création d'un produit (nécessite authentification admin)
     */
    public function testProductCreateRequiresAdminAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/products/new');

        // Redirection vers la page de connexion
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test de l'édition d'un produit (nécessite authentification admin)
     */
    public function testProductEditRequiresAdminAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/products/1/edit');

        // Redirection vers la page de connexion
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test de la suppression d'un produit (nécessite authentification admin)
     */
    public function testProductDeleteRequiresAdminAuthentication(): void
    {
        $client = static::createClient();
        $client->request('POST', '/admin/products/1/delete');

        // Redirection vers la page de connexion
        $this->assertResponseRedirects('/login');
    }

    /**
     * Test de la validation des paramètres de l'API
     */
    public function testProductApiWithInvalidParameters(): void
    {
        $client = static::createClient();
        
        // Test avec un ID invalide
        $client->request('GET', '/products/api/invalid-id');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        
        // Test avec des paramètres de pagination invalides
        $client->request('GET', '/products/api?page=-1');
        $this->assertResponseIsSuccessful(); // Doit gérer gracieusement les paramètres invalides
    }

    /**
     * Test des en-têtes de sécurité sur les pages produits
     */
    public function testProductPagesSecurityHeaders(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products');
        
        $response = $client->getResponse();
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
    }

    /**
     * Test de la performance de l'API des produits
     */
    public function testProductApiPerformance(): void
    {
        $client = static::createClient();
        
        $startTime = microtime(true);
        $client->request('GET', '/products/api');
        $endTime = microtime(true);
        
        $responseTime = $endTime - $startTime;
        
        $this->assertResponseIsSuccessful();
        $this->assertLessThan(1.0, $responseTime, 'La réponse de l\'API doit être rapide (< 1 seconde)');
    }
}
