# Tests Unitaires - BoutiqueProd

## 📋 Résumé des tests existants et créés

### ✅ Tests existants trouvés :

1. **`tests/Api/ProductApiTest.php`** - Tests d'intégration pour l'API des produits
2. **`tests/Security/HeadersTest.php`** - Tests de sécurité pour les en-têtes HTTP
3. **`tests/bootstrap.php`** - Configuration de bootstrap pour les tests

### 🆕 Tests unitaires créés :

4. **`tests/Entity/ProductTest.php`** - Tests unitaires pour l'entité Product
5. **`tests/Entity/UserTest.php`** - Tests unitaires pour l'entité User  
6. **`tests/Entity/CategoryTest.php`** - Tests unitaires pour l'entité Category
7. **`tests/Service/ProductServiceTest.php`** - Tests unitaires pour le service ProductService
8. **`tests/Controller/ProductControllerTest.php`** - Tests d'intégration pour le contrôleur ProductController

## 🚀 Comment exécuter les tests

### Prérequis
- PHP 8.1+
- PHPUnit 9.5+
- Extensions PHP requises : dom, json, libxml, mbstring, tokenizer, xml, xmlwriter

### Commandes d'exécution

```bash
# Exécuter tous les tests
php bin/phpunit

# Exécuter les tests d'une classe spécifique
php bin/phpunit tests/Entity/ProductTest.php

# Exécuter les tests avec couverture de code
php bin/phpunit --coverage-html coverage/

# Exécuter les tests en mode verbose
php bin/phpunit --verbose

# Exécuter les tests d'une suite spécifique
php bin/phpunit --testsuite="Entity"
```

## 📊 Couverture des tests

### Entités testées :
- ✅ **Product** - 15 tests couvrant :
  - Création et valeurs par défaut
  - Getters/setters basiques
  - Calcul du prix avec TVA (20%)
  - Gestion du stock
  - Gestion des images (ajout/suppression)
  - Propriétés booléennes (isActive, isFeatured)
  - Associations (Category, OrderItems)
  - Chaînage des méthodes

- ✅ **User** - 16 tests couvrant :
  - Création et valeurs par défaut
  - Gestion des rôles (avec ROLE_USER automatique)
  - Authentification (UserInterface)
  - Informations personnelles
  - Méthode getFullName()
  - Associations (Orders)
  - Validation des emails

- ✅ **Category** - 12 tests couvrant :
  - Création et valeurs par défaut
  - Gestion des produits associés
  - Propriétés booléennes
  - Associations bidirectionnelles

### Services testés :
- ✅ **ProductService** - 10 tests couvrant :
  - Recherche de produits
  - Filtrage par catégorie
  - Produits en vedette
  - Calculs de prix
  - Validation de disponibilité
  - Mise à jour du stock
  - Statistiques

### Contrôleurs testés :
- ✅ **ProductController** - 15 tests couvrant :
  - Pages web (index, show, search)
  - API endpoints (JSON)
  - Gestion des erreurs (404)
  - Authentification requise
  - En-têtes de sécurité
  - Performance

## 🎯 Exemples de résultats attendus

### Test réussi :
```
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

..                                                                   2 / 2 (100%)

Time: 00:00.123, Memory: 6.00 MB

OK (2 tests, 4 assertions)
```

### Test avec échec :
```
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: 00:00.045, Memory: 6.00 MB

There was 1 failure:

1) App\Tests\Entity\ProductTest::testPriceWithTva
Failed asserting that 120.0 matches expected 100.0.

/path/to/tests/Entity/ProductTest.php:45

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
```

## 🔧 Configuration

Le fichier `phpunit.xml.dist` configure :
- Bootstrap : `tests/bootstrap.php`
- Environnement de test : `APP_ENV=test`
- Couverture de code pour le dossier `src/`
- Écouteur Symfony pour les tests

## 📝 Notes importantes

1. **Tests unitaires** : Testent les entités et services de manière isolée
2. **Tests d'intégration** : Testent les contrôleurs et l'API avec le framework Symfony
3. **Mocks** : Utilisés pour isoler les dépendances dans les tests de services
4. **Assertions** : Vérifient les comportements attendus avec des messages clairs
5. **Couverture** : Les tests couvrent les cas de succès et d'échec

## 🚨 Résolution des problèmes

Si vous rencontrez des erreurs lors de l'exécution des tests :

1. **Extensions PHP manquantes** : Installez les extensions requises
2. **Base de données** : Configurez une base de données de test
3. **Cache** : Videz le cache Symfony : `php bin/console cache:clear --env=test`
4. **Permissions** : Vérifiez les permissions sur les dossiers `var/` et `public/uploads/`
