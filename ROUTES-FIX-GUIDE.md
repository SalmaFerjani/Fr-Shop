# Guide de Correction des Routes - La Boutique Française

## ✅ Problème résolu

L'erreur `Cannot autowire argument $product of "App\Controller\Admin\ProductAdminController::edit()": it references class "App\Entity\Product" but no such service exists.` était causée par des routes incorrectes avec des chemins dupliqués.

## 🔧 Corrections apportées

### **Problème identifié**
Les contrôleurs avaient des préfixes de route dans la classe ET dans les méthodes individuelles, ce qui créait des URLs dupliquées :

**Avant (incorrect) :**
```php
#[Route('/admin/products')]  // Préfixe de classe
class ProductAdminController extends AbstractController
{
    #[Route('/admin/products/edit/{id}', ...)]  // Chemin dupliqué !
    public function edit(...)
}
```

**Résultat :** `/admin/products/admin/products/edit/{id}` ❌

### **Solution appliquée**
**Après (correct) :**
```php
#[Route('/admin/products')]  // Préfixe de classe
class ProductAdminController extends AbstractController
{
    #[Route('/edit/{id}', ...)]  // Chemin relatif uniquement
    public function edit(...)
}
```

**Résultat :** `/admin/products/edit/{id}` ✅

## 📋 Routes finales

### **CategoryAdminController**
```php
#[Route('/admin/categories')]  // Préfixe de classe
class CategoryAdminController extends AbstractController
{
    #[Route('/', name: 'admin_category_index')]                    // GET /admin/categories
    #[Route('/new', name: 'admin_category_new')]                   // GET /admin/categories/new
    #[Route('/edit/{id}', name: 'admin_categories_edit', methods: ['GET', 'POST'])]    // GET/POST /admin/categories/edit/{id}
    #[Route('/delete/{id}', name: 'admin_categories_delete', methods: ['POST'])]       // POST /admin/categories/delete/{id}
}
```

### **ProductAdminController**
```php
#[Route('/admin/products')]  // Préfixe de classe
class ProductAdminController extends AbstractController
{
    #[Route('/', name: 'admin_product_index')]                     // GET /admin/products
    #[Route('/new', name: 'admin_product_new')]                    // GET /admin/products/new
    #[Route('/edit/{id}', name: 'admin_products_edit', methods: ['GET', 'POST'])]     // GET/POST /admin/products/edit/{id}
    #[Route('/delete/{id}', name: 'admin_products_delete', methods: ['POST'])]        // POST /admin/products/delete/{id}
}
```

## 🎯 URLs fonctionnelles

### **Catégories**
- **Liste** : `GET /admin/categories`
- **Nouvelle** : `GET /admin/categories/new`
- **Éditer** : `GET /admin/categories/edit/5`
- **Modifier** : `POST /admin/categories/edit/5`
- **Supprimer** : `POST /admin/categories/delete/5`

### **Produits**
- **Liste** : `GET /admin/products`
- **Nouveau** : `GET /admin/products/new`
- **Éditer** : `GET /admin/products/edit/10`
- **Modifier** : `POST /admin/products/edit/10`
- **Supprimer** : `POST /admin/products/delete/10`

## 🔧 ParamConverter automatique

Maintenant que les routes sont correctes, Symfony peut utiliser le ParamConverter :

1. **Détection de l'ID** : Symfony voit `{id}` dans l'URL
2. **Type de paramètre** : Symfony détecte `Category $category` ou `Product $product`
3. **Injection automatique** : Symfony fait `$repository->find($id)` automatiquement
4. **Injection de l'objet** : L'entité est injectée directement dans la méthode

## 📁 Templates Twig mis à jour

Les templates utilisent les bonnes routes :

```html
<!-- Catégories -->
<a href="{{ path('admin_categories_edit', {id: category.id}) }}">Modifier</a>
<form method="post" action="{{ path('admin_categories_delete', {id: category.id}) }}">
    <input type="hidden" name="_token" value="{{ csrf_token('delete_category_' ~ category.id) }}">
    <button class="btn btn-danger">Supprimer</button>
</form>

<!-- Produits -->
<a href="{{ path('admin_products_edit', {id: product.id}) }}">Modifier</a>
<form method="post" action="{{ path('admin_products_delete', {id: product.id}) }}">
    <input type="hidden" name="_token" value="{{ csrf_token('delete_product_' ~ product.id) }}">
    <button class="btn btn-danger">Supprimer</button>
</form>
```

## 🛡️ Configuration services.yaml

La configuration est correcte :

```yaml
services:
    App\:
        resource: '../src/'
        exclude:
            - '../src/Entity/'  # ✅ Entités exclues de l'autowiring
```

## 🧹 Cache vidé

Le cache Symfony a été vidé pour appliquer les changements :
- ✅ Suppression de `var/cache/*`
- ✅ Symfony recréera le cache avec les nouvelles routes
- ✅ ParamConverter sera maintenant fonctionnel

## 🎉 Résultat final

Plus d'erreurs :
- ✅ **Plus d'erreur d'autowiring** - Les entités ne sont pas des services
- ✅ **Plus d'erreur 404** - Les routes sont correctement définies
- ✅ **ParamConverter fonctionnel** - Injection automatique des entités
- ✅ **URLs propres** - `/admin/categories/edit/5` au lieu de `/admin/categories/admin/categories/edit/5`

Les contrôleurs utilisent maintenant correctement le ParamConverter de Symfony ! 🚀
