<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-products',
    description: 'Insère automatiquement des produits et catégories dans la base de données',
)]
class SeedProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🌱 Insertion des produits et catégories');

        try {
            // Créer les catégories
            $categories = $this->createCategories();
            $io->success(sprintf('✅ %d catégories créées', count($categories)));

            // Créer les produits
            $products = $this->createProducts($categories);
            $io->success(sprintf('✅ %d produits créés', count($products)));

            // Sauvegarder en base
            $this->entityManager->flush();

            $io->success('🎉 Toutes les données ont été insérées avec succès !');
            
            // Afficher un résumé
            $this->displaySummary($io, $categories, $products);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de l\'insertion des données : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function createCategories(): array
    {
        $categoriesData = [
            [
                'name' => 'Sacs',
                'description' => 'Collection de sacs élégants pour toutes les occasions',
                'image' => 'sacs-category.jpg'
            ],
            [
                'name' => 'Bijoux',
                'description' => 'Bijoux raffinés et accessoires de mode',
                'image' => 'bijoux-category.jpg'
            ],
            [
                'name' => 'Chaussures',
                'description' => 'Chaussures confortables et stylées',
                'image' => 'chaussures-category.jpg'
            ]
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = new Category();
            $category->setName($data['name'])
                    ->setDescription($data['description'])
                    ->setImage($data['image'])
                    ->setIsActive(true);

            $this->entityManager->persist($category);
            $categories[] = $category;
        }

        return $categories;
    }

    private function createProducts(array $categories): array
    {
        $productsData = [
            // Sacs
            [
                'name' => 'Sac à main cuir noir',
                'description' => 'Sac à main en cuir véritable noir, parfait pour le quotidien. Compartiments multiples et fermeture sécurisée.',
                'price' => 89.99,
                'stock' => 15,
                'sku' => 'SAC-001',
                'mainImage' => 'sac-cuir-noir.jpg',
                'images' => ['sac-cuir-noir-1.jpg', 'sac-cuir-noir-2.jpg'],
                'isFeatured' => true,
                'category' => 'Sacs'
            ],
            [
                'name' => 'Sac bandoulière marron',
                'description' => 'Sac bandoulière en cuir marron avec sangle réglable. Style vintage et intemporel.',
                'price' => 75.50,
                'stock' => 8,
                'sku' => 'SAC-002',
                'mainImage' => 'sac-bandouliere-marron.jpg',
                'images' => ['sac-bandouliere-marron-1.jpg'],
                'isFeatured' => false,
                'category' => 'Sacs'
            ],
            [
                'name' => 'Sac cabas écologique',
                'description' => 'Grand sac cabas en coton bio, idéal pour les courses ou la plage. Respectueux de l\'environnement.',
                'price' => 45.00,
                'stock' => 25,
                'sku' => 'SAC-003',
                'mainImage' => 'sac-cabas-eco.jpg',
                'images' => ['sac-cabas-eco-1.jpg', 'sac-cabas-eco-2.jpg'],
                'isFeatured' => true,
                'category' => 'Sacs'
            ],
            [
                'name' => 'Pochette soirée dorée',
                'description' => 'Élégante pochette dorée pour les soirées. Fermeture à glissière et chaîne dorée.',
                'price' => 65.00,
                'stock' => 12,
                'sku' => 'SAC-004',
                'mainImage' => 'pochette-soiree-doree.jpg',
                'images' => ['pochette-soiree-doree-1.jpg'],
                'isFeatured' => false,
                'category' => 'Sacs'
            ],

            // Bijoux
            [
                'name' => 'Collier perles fines',
                'description' => 'Collier en perles fines de culture, longueur 45cm. Élégant et raffiné pour toutes les occasions.',
                'price' => 120.00,
                'stock' => 6,
                'sku' => 'BIJ-001',
                'mainImage' => 'collier-perles-fines.jpg',
                'images' => ['collier-perles-fines-1.jpg', 'collier-perles-fines-2.jpg'],
                'isFeatured' => true,
                'category' => 'Bijoux'
            ],
            [
                'name' => 'Boucles d\'oreilles argent',
                'description' => 'Boucles d\'oreilles en argent sterling avec motif géométrique. Design moderne et intemporel.',
                'price' => 55.00,
                'stock' => 18,
                'sku' => 'BIJ-002',
                'mainImage' => 'boucles-oreilles-argent.jpg',
                'images' => ['boucles-oreilles-argent-1.jpg'],
                'isFeatured' => false,
                'category' => 'Bijoux'
            ],
            [
                'name' => 'Bracelet cuir et métal',
                'description' => 'Bracelet mixte en cuir et métal doré. Fermoir réglable, style casual chic.',
                'price' => 35.00,
                'stock' => 20,
                'sku' => 'BIJ-003',
                'mainImage' => 'bracelet-cuir-metal.jpg',
                'images' => ['bracelet-cuir-metal-1.jpg', 'bracelet-cuir-metal-2.jpg'],
                'isFeatured' => false,
                'category' => 'Bijoux'
            ],
            [
                'name' => 'Bague solitaire diamant',
                'description' => 'Bague solitaire avec diamant 0.5 carat, monture en or blanc 18k. Parfaite pour les occasions spéciales.',
                'price' => 850.00,
                'stock' => 2,
                'sku' => 'BIJ-004',
                'mainImage' => 'bague-solitaire-diamant.jpg',
                'images' => ['bague-solitaire-diamant-1.jpg', 'bague-solitaire-diamant-2.jpg'],
                'isFeatured' => true,
                'category' => 'Bijoux'
            ],

            // Chaussures
            [
                'name' => 'Baskets blanches classiques',
                'description' => 'Baskets en cuir blanc avec semelle en caoutchouc. Confortables et polyvalentes pour tous les styles.',
                'price' => 95.00,
                'stock' => 22,
                'sku' => 'CHX-001',
                'mainImage' => 'baskets-blanches-classiques.jpg',
                'images' => ['baskets-blanches-1.jpg', 'baskets-blanches-2.jpg'],
                'isFeatured' => true,
                'category' => 'Chaussures'
            ],
            [
                'name' => 'Escarpins noirs vernis',
                'description' => 'Escarpins noirs en cuir vernis, talon 8cm. Élégants et confortables pour le bureau ou les soirées.',
                'price' => 125.00,
                'stock' => 10,
                'sku' => 'CHX-002',
                'mainImage' => 'escarpins-noirs-vernis.jpg',
                'images' => ['escarpins-noirs-1.jpg'],
                'isFeatured' => false,
                'category' => 'Chaussures'
            ],
            [
                'name' => 'Bottes cuir marron',
                'description' => 'Bottes en cuir marron avec doublure en fourrure. Parfaites pour l\'automne et l\'hiver.',
                'price' => 180.00,
                'stock' => 7,
                'sku' => 'CHX-003',
                'mainImage' => 'bottes-cuir-marron.jpg',
                'images' => ['bottes-cuir-marron-1.jpg', 'bottes-cuir-marron-2.jpg'],
                'isFeatured' => true,
                'category' => 'Chaussures'
            ],
            [
                'name' => 'Sandales été dorées',
                'description' => 'Sandales plates dorées avec lanières fines. Idéales pour l\'été et les vacances.',
                'price' => 45.00,
                'stock' => 15,
                'sku' => 'CHX-004',
                'mainImage' => 'sandales-ete-dorees.jpg',
                'images' => ['sandales-ete-dorees-1.jpg'],
                'isFeatured' => false,
                'category' => 'Chaussures'
            ]
        ];

        $products = [];
        $categoryMap = [];
        
        // Créer un mapping des catégories par nom
        foreach ($categories as $category) {
            $categoryMap[$category->getName()] = $category;
        }

        foreach ($productsData as $data) {
            $product = new Product();
            $product->setName($data['name'])
                   ->setDescription($data['description'])
                   ->setPrice($data['price'])
                   ->setStock($data['stock'])
                   ->setSku($data['sku'])
                   ->setMainImage($data['mainImage'])
                   ->setImages($data['images'])
                   ->setIsFeatured($data['isFeatured'])
                   ->setIsActive(true)
                   ->setCategory($categoryMap[$data['category']]);

            $this->entityManager->persist($product);
            $products[] = $product;
        }

        return $products;
    }

    private function displaySummary(SymfonyStyle $io, array $categories, array $products): void
    {
        $io->section('📊 Résumé de l\'insertion');

        // Résumé par catégorie
        $categoryCounts = [];
        foreach ($products as $product) {
            $categoryName = $product->getCategory()->getName();
            $categoryCounts[$categoryName] = ($categoryCounts[$categoryName] ?? 0) + 1;
        }

        $io->table(
            ['Catégorie', 'Nombre de produits'],
            array_map(
                fn($name, $count) => [$name, $count],
                array_keys($categoryCounts),
                array_values($categoryCounts)
            )
        );

        // Produits vedettes
        $featuredProducts = array_filter($products, fn($p) => $p->isIsFeatured());
        if (!empty($featuredProducts)) {
            $io->section('⭐ Produits vedettes');
            foreach ($featuredProducts as $product) {
                $io->text(sprintf('• %s - %.2f€', $product->getName(), $product->getPrice()));
            }
        }

        $io->success('🎯 Commandes disponibles :');
        $io->text('• php bin/console app:seed-products - Insérer les données');
        $io->text('• php bin/console doctrine:query:sql "SELECT COUNT(*) FROM product" - Compter les produits');
        $io->text('• php bin/console doctrine:query:sql "SELECT COUNT(*) FROM category" - Compter les catégories');
    }
}