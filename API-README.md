# API Documentation - La Boutique Française

## Configuration Swagger

L'API a été configurée avec Swagger/OpenAPI pour faciliter les tests et la documentation.

### Endpoints disponibles

#### 1. Documentation Swagger
- **URL**: `/api/doc`
- **Description**: Interface Swagger pour tester l'API
- **Méthode**: GET

#### 2. Spécification OpenAPI
- **URL**: `/api/spec`
- **Description**: Spécification OpenAPI au format JSON
- **Méthode**: GET

#### 3. Liste des produits
- **URL**: `/api/products`
- **Description**: Récupère tous les produits actifs
- **Méthode**: GET
- **Paramètres de requête**:
  - `category` (optionnel): ID de la catégorie pour filtrer
  - `search` (optionnel): Terme de recherche

**Exemple de requête**:
```
GET /api/products?category=1&search=sac
```

**Exemple de réponse**:
```json
[
  {
    "id": 1,
    "name": "Sac en cuir",
    "description": "Magnifique sac en cuir français",
    "price": 150.00,
    "image": "sac-cuir.jpg",
    "category": {
      "id": 1,
      "name": "Sacs"
    }
  }
]
```

#### 4. Détails d'un produit
- **URL**: `/api/products/{id}`
- **Description**: Récupère les détails d'un produit spécifique
- **Méthode**: GET
- **Paramètres**:
  - `id`: ID du produit (entier)

**Exemple de requête**:
```
GET /api/products/1
```

**Exemple de réponse**:
```json
{
  "id": 1,
  "name": "Sac en cuir",
  "description": "Magnifique sac en cuir français",
  "price": 150.00,
  "image": "sac-cuir.jpg",
  "category": {
    "id": 1,
    "name": "Sacs"
  }
}
```

## Tests

Des tests unitaires ont été créés dans `tests/Api/ProductApiTest.php` pour tester tous les endpoints.

### Exécuter les tests

```bash
php bin/phpunit tests/Api/ProductApiTest.php
```

## Suppression des icônes

Toutes les icônes Font Awesome ont été supprimées des headers de navigation pour simplifier l'interface :

- ✅ Suppression des CDN Font Awesome
- ✅ Suppression des icônes de navigation
- ✅ Suppression des icônes du footer
- ✅ Suppression des icônes des menus utilisateur

## Utilisation

1. **Accéder à la documentation Swagger**: Visitez `http://localhost:8000/api/doc`
2. **Tester l'API**: Utilisez l'interface Swagger pour tester les endpoints
3. **Intégration**: Utilisez les endpoints JSON pour intégrer l'API dans d'autres applications

## Structure des réponses

Toutes les réponses API sont au format JSON avec les codes de statut HTTP appropriés :
- `200`: Succès
- `404`: Ressource non trouvée
- `500`: Erreur serveur

Les erreurs sont retournées au format :
```json
{
  "error": "Message d'erreur"
}
```
