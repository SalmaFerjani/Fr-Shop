<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@boutiquefrancaise.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('Boutique');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPhone('0123456789');
        $admin->setAddress('123 Rue de la Mode');
        $admin->setPostalCode('75001');
        $admin->setCity('Paris');
        $admin->setCountry('France');
        $manager->persist($admin);

        // Créer un utilisateur test
        $user = new User();
        $user->setEmail('user@test.fr');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setPhone('0987654321');
        $user->setAddress('456 Avenue des Champs');
        $user->setPostalCode('75008');
        $user->setCity('Paris');
        $user->setCountry('France');
        $manager->persist($user);

        // Créer les catégories
        $sacsCategory = new Category();
        $sacsCategory->setName('Sacs');
        $sacsCategory->setDescription('Sacs élégants fabriqués en France avec des matériaux nobles');
        $sacsCategory->setImage('https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400');
        $manager->persist($sacsCategory);

        $bijouxCategory = new Category();
        $bijouxCategory->setName('Bijoux');
        $bijouxCategory->setDescription('Bijoux raffinés créés par des artisans joailliers français');
        $bijouxCategory->setImage('https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400');
        $manager->persist($bijouxCategory);

        // Créer les produits - Sacs
        $sacs = [
            [
                'name' => 'Sac Cuir Parisien',
                'description' => 'Sac en cuir véritable fabriqué à Paris. Design élégant et intemporel, parfait pour toutes les occasions.',
                'price' => 89.99,
                'stock' => 15,
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400'
            ],
            [
                'name' => 'Tote Bag Élégant',
                'description' => 'Tote bag en toile de lin française avec finitions en cuir. Spacieux et pratique pour le quotidien.',
                'price' => 45.99,
                'stock' => 25,
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1594223274512-ad4803739b7c?w=400'
            ],
            [
                'name' => 'Sac de Soirée Luxe',
                'description' => 'Sac de soirée en velours avec broderies dorées. Pièce unique pour les occasions spéciales.',
                'price' => 129.99,
                'stock' => 8,
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=400'
            ],
            [
                'name' => 'Sac Bandoulière Moderne',
                'description' => 'Sac bandoulière en cuir avec design contemporain. Parfait pour un look urbain et sophistiqué.',
                'price' => 75.99,
                'stock' => 12,
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400'
            ]
        ];

        foreach ($sacs as $sacData) {
            $product = new Product();
            $product->setName($sacData['name']);
            $product->setDescription($sacData['description']);
            $product->setPrice($sacData['price']);
            $product->setStock($sacData['stock']);
            $product->setIsFeatured($sacData['featured']);
            $product->setMainImage($sacData['image']);
            $product->setCategory($sacsCategory);
            $product->setSku('SAC-' . strtoupper(substr($sacData['name'], 0, 3)) . '-' . rand(100, 999));
            $manager->persist($product);
        }

        // Créer les produits - Bijoux
        $bijoux = [
            [
                'name' => 'Bracelet Élégance',
                'description' => 'Bracelet en argent sterling avec perles de culture. Design délicat et raffiné.',
                'price' => 65.99,
                'stock' => 20,
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400'
            ],
            [
                'name' => 'Collier Parisien',
                'description' => 'Collier en or 18 carats avec pendentif en forme de Tour Eiffel. Symbole de l\'élégance française.',
                'price' => 189.99,
                'stock' => 10,
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=400'
            ],
            [
                'name' => 'Bagues Artisanales',
                'description' => 'Collection de bagues en argent avec pierres semi-précieuses. Chaque pièce est unique.',
                'price' => 45.99,
                'stock' => 30,
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1602751584552-8ba73aad10e1?w=400'
            ],
            [
                'name' => 'Boucles d\'Oreilles Fleur',
                'description' => 'Boucles d\'oreilles en forme de fleur avec cristaux Swarovski. Élégance et féminité.',
                'price' => 35.99,
                'stock' => 18,
                'featured' => false,
                'image' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=400'
            ],
            [
                'name' => 'Montre Classique',
                'description' => 'Montre suisse avec bracelet en cuir français. Précision et élégance intemporelle.',
                'price' => 299.99,
                'stock' => 5,
                'featured' => true,
                'image' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=400'
            ]
        ];

        foreach ($bijoux as $bijouData) {
            $product = new Product();
            $product->setName($bijouData['name']);
            $product->setDescription($bijouData['description']);
            $product->setPrice($bijouData['price']);
            $product->setStock($bijouData['stock']);
            $product->setIsFeatured($bijouData['featured']);
            $product->setMainImage($bijouData['image']);
            $product->setCategory($bijouxCategory);
            $product->setSku('BIJ-' . strtoupper(substr($bijouData['name'], 0, 3)) . '-' . rand(100, 999));
            $manager->persist($product);
        }

        $manager->flush();
    }
} 