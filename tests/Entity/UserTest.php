<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Order;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité User
 * 
 * Ces tests vérifient le comportement des méthodes métier de l'entité User,
 * notamment la gestion des rôles, l'authentification, et les informations personnelles.
 */
class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    /**
     * Test de création d'un utilisateur avec des valeurs par défaut
     */
    public function testUserCreationWithDefaults(): void
    {
        $this->assertTrue($this->user->isIsActive());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getUpdatedAt());
        $this->assertIsArray($this->user->getRoles());
        $this->assertEmpty($this->user->getRoles());
    }

    /**
     * Test des getters et setters basiques
     */
    public function testBasicGettersAndSetters(): void
    {
        // Test de l'email
        $this->user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $this->user->getEmail());

        // Test du prénom
        $this->user->setFirstName('Jean');
        $this->assertEquals('Jean', $this->user->getFirstName());

        // Test du nom
        $this->user->setLastName('Dupont');
        $this->assertEquals('Dupont', $this->user->getLastName());

        // Test du téléphone
        $this->user->setPhone('0123456789');
        $this->assertEquals('0123456789', $this->user->getPhone());

        // Test de l'adresse
        $this->user->setAddress('123 Rue de la Paix');
        $this->assertEquals('123 Rue de la Paix', $this->user->getAddress());

        // Test du code postal
        $this->user->setPostalCode('75001');
        $this->assertEquals('75001', $this->user->getPostalCode());

        // Test de la ville
        $this->user->setCity('Paris');
        $this->assertEquals('Paris', $this->user->getCity());

        // Test du pays
        $this->user->setCountry('France');
        $this->assertEquals('France', $this->user->getCountry());
    }

    /**
     * Test de l'identifiant utilisateur (UserInterface)
     */
    public function testUserIdentifier(): void
    {
        $this->user->setEmail('user@example.com');
        $this->assertEquals('user@example.com', $this->user->getUserIdentifier());
    }

    /**
     * Test de la gestion des rôles
     */
    public function testRolesManagement(): void
    {
        // Test avec des rôles personnalisés
        $this->user->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);
        $roles = $this->user->getRoles();
        
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_EDITOR', $roles);
        $this->assertContains('ROLE_USER', $roles); // ROLE_USER doit toujours être présent
        $this->assertCount(3, $roles);
    }

    /**
     * Test des rôles avec doublons
     */
    public function testRolesWithDuplicates(): void
    {
        $this->user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_USER']);
        $roles = $this->user->getRoles();
        
        // Les doublons doivent être supprimés
        $this->assertCount(2, $roles);
        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    /**
     * Test des rôles vides (doit toujours contenir ROLE_USER)
     */
    public function testEmptyRoles(): void
    {
        $this->user->setRoles([]);
        $roles = $this->user->getRoles();
        
        $this->assertCount(1, $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    /**
     * Test de la gestion du mot de passe
     */
    public function testPasswordManagement(): void
    {
        $password = 'motdepasse123';
        $this->user->setPassword($password);
        
        $this->assertEquals($password, $this->user->getPassword());
    }

    /**
     * Test de la méthode getFullName
     */
    public function testGetFullName(): void
    {
        $this->user->setFirstName('Marie');
        $this->user->setLastName('Martin');
        
        $this->assertEquals('Marie Martin', $this->user->getFullName());
    }

    /**
     * Test de getFullName avec des noms vides
     */
    public function testGetFullNameWithEmptyNames(): void
    {
        $this->user->setFirstName('');
        $this->user->setLastName('');
        
        $this->assertEquals(' ', $this->user->getFullName());
    }

    /**
     * Test de la propriété isActive
     */
    public function testIsActiveProperty(): void
    {
        $this->user->setIsActive(false);
        $this->assertFalse($this->user->isIsActive());

        $this->user->setIsActive(true);
        $this->assertTrue($this->user->isIsActive());
    }

    /**
     * Test de l'association avec des commandes
     */
    public function testOrdersAssociation(): void
    {
        $order = new Order();
        $order->setTotal(99.99);
        
        $this->user->addOrder($order);
        
        $this->assertTrue($this->user->getOrders()->contains($order));
        $this->assertSame($this->user, $order->getUser());
    }

    /**
     * Test de la suppression d'une commande
     */
    public function testRemoveOrder(): void
    {
        $order = new Order();
        $order->setTotal(49.99);
        
        $this->user->addOrder($order);
        $this->assertTrue($this->user->getOrders()->contains($order));
        
        $this->user->removeOrder($order);
        $this->assertFalse($this->user->getOrders()->contains($order));
        $this->assertNull($order->getUser());
    }

    /**
     * Test de la gestion des dates
     */
    public function testDateManagement(): void
    {
        $newDate = new \DateTimeImmutable('2024-01-01');
        
        $this->user->setCreatedAt($newDate);
        $this->assertEquals($newDate, $this->user->getCreatedAt());
        
        $this->user->setUpdatedAt($newDate);
        $this->assertEquals($newDate, $this->user->getUpdatedAt());
    }

    /**
     * Test de la méthode eraseCredentials (UserInterface)
     */
    public function testEraseCredentials(): void
    {
        // Cette méthode ne fait rien pour le moment, mais on teste qu'elle ne génère pas d'erreur
        $this->user->eraseCredentials();
        $this->assertTrue(true); // Si on arrive ici, c'est que la méthode s'est exécutée sans erreur
    }

    /**
     * Test du chaînage des méthodes (fluent interface)
     */
    public function testMethodChaining(): void
    {
        $result = $this->user
            ->setEmail('test@example.com')
            ->setFirstName('Pierre')
            ->setLastName('Durand')
            ->setIsActive(true);
        
        $this->assertSame($this->user, $result);
        $this->assertEquals('test@example.com', $this->user->getEmail());
        $this->assertEquals('Pierre', $this->user->getFirstName());
        $this->assertEquals('Durand', $this->user->getLastName());
        $this->assertTrue($this->user->isIsActive());
    }

    /**
     * Test de la validation des contraintes d'email
     */
    public function testEmailValidation(): void
    {
        // Test avec un email valide
        $this->user->setEmail('valid@example.com');
        $this->assertEquals('valid@example.com', $this->user->getEmail());

        // Test avec un email avec sous-domaine
        $this->user->setEmail('user@subdomain.example.com');
        $this->assertEquals('user@subdomain.example.com', $this->user->getEmail());
    }

    /**
     * Test de la gestion des informations d'adresse complètes
     */
    public function testCompleteAddressInformation(): void
    {
        $this->user
            ->setAddress('456 Avenue des Champs-Élysées')
            ->setPostalCode('75008')
            ->setCity('Paris')
            ->setCountry('France');

        $this->assertEquals('456 Avenue des Champs-Élysées', $this->user->getAddress());
        $this->assertEquals('75008', $this->user->getPostalCode());
        $this->assertEquals('Paris', $this->user->getCity());
        $this->assertEquals('France', $this->user->getCountry());
    }
}
