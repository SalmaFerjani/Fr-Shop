<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiSpecController extends AbstractController
{
    #[Route('/api/spec', name: 'api_spec')]
    public function spec(): JsonResponse
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'API Boutique Française',
                'version' => '1.0.0',
                'description' => 'API pour la gestion des produits de la boutique française',
                'contact' => [
                    'email' => 'contact@boutiquefrancaise.fr'
                ]
            ],
            'servers' => [
                [
                    'url' => 'http://localhost:8000',
                    'description' => 'Serveur de développement'
                ]
            ],
            'paths' => [
                '/api/products' => [
                    'get' => [
                        'tags' => ['Products'],
                        'summary' => 'Récupérer la liste des produits',
                        'description' => 'Récupère tous les produits actifs avec possibilité de filtrage par catégorie et recherche',
                        'parameters' => [
                            [
                                'name' => 'category',
                                'in' => 'query',
                                'description' => 'ID de la catégorie pour filtrer les produits',
                                'required' => false,
                                'schema' => ['type' => 'integer']
                            ],
                            [
                                'name' => 'search',
                                'in' => 'query',
                                'description' => 'Terme de recherche pour filtrer les produits',
                                'required' => false,
                                'schema' => ['type' => 'string']
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Liste des produits récupérée avec succès',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string'],
                                                    'description' => ['type' => 'string'],
                                                    'price' => ['type' => 'number'],
                                                    'image' => ['type' => 'string'],
                                                    'category' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'id' => ['type' => 'integer'],
                                                            'name' => ['type' => 'string']
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                '/api/products/{id}' => [
                    'get' => [
                        'tags' => ['Products'],
                        'summary' => 'Récupérer un produit par son ID',
                        'description' => 'Récupère les détails d\'un produit spécifique',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'description' => 'ID du produit',
                                'required' => true,
                                'schema' => ['type' => 'integer']
                            ]
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Produit récupéré avec succès',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => ['type' => 'integer'],
                                                'name' => ['type' => 'string'],
                                                'description' => ['type' => 'string'],
                                                'price' => ['type' => 'number'],
                                                'image' => ['type' => 'string'],
                                                'category' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'id' => ['type' => 'integer'],
                                                        'name' => ['type' => 'string']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            '404' => [
                                'description' => 'Produit non trouvé'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return new JsonResponse($spec);
    }
}
