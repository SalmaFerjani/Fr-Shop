<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductApiTest extends WebTestCase
{
    public function testGetProducts(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetProductsWithCategoryFilter(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api?category=1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetProductsWithSearch(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api?search=test');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetProductById(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('price', $data);
    }

    public function testGetProductByIdNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/products/api/999999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testApiSpecEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/spec');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('openapi', $data);
        $this->assertArrayHasKey('info', $data);
        $this->assertArrayHasKey('paths', $data);
    }

    public function testSwaggerDocumentationPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/doc');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('title', 'API Documentation');
    }
}
