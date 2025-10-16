<?php

namespace App\Controller;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="API Boutique Française",
 *     version="1.0.0",
 *     description="API pour la gestion des produits de la boutique française",
 *     @OA\Contact(
 *         email="contact@boutiquefrancaise.fr"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur de développement"
 * )
 * @OA\Tag(
 *     name="Products",
 *     description="Gestion des produits"
 * )
 */
class ApiController
{
    // Ce contrôleur sert uniquement à définir les métadonnées globales de l'API
}
