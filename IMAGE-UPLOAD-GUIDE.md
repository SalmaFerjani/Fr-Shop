# Guide d'Upload d'Images - La Boutique Française

## ✅ Fonctionnalités implémentées

Les formulaires de catégories et de produits permettent maintenant aux utilisateurs d'ajouter des images de deux façons :
1. **Upload depuis le PC** - Utilisation d'un champ `<input type="file">`
2. **URL d'image** - Saisie d'une URL d'image externe

## 🔧 Améliorations apportées

### **1. Formulaires mis à jour**

#### **CategoryType.php**
```php
->add('imageFile', FileType::class, [
    'label' => 'Image de la catégorie',
    'mapped' => false,
    'required' => false,
    'attr' => [
        'class' => 'form-control',
        'accept' => 'image/*',
        'onchange' => 'previewImage(this)'
    ],
    'row_attr' => ['class' => 'mb-3']
])
->add('image', TextType::class, [
    'label' => 'URL de l\'image (optionnel)',
    'required' => false,
    'attr' => [
        'class' => 'form-control',
        'placeholder' => 'https://example.com/image.jpg'
    ],
    'row_attr' => ['class' => 'mb-3']
])
```

#### **ProductType.php**
```php
->add('mainImageFile', FileType::class, [
    'label' => 'Image principale',
    'mapped' => false,
    'required' => false,
    'attr' => [
        'class' => 'form-control',
        'accept' => 'image/*',
        'onchange' => 'previewImage(this)'
    ],
    'row_attr' => ['class' => 'mb-3']
])
->add('mainImage', TextType::class, [
    'label' => 'URL de l\'image principale (optionnel)',
    'required' => false,
    'attr' => [
        'class' => 'form-control',
        'placeholder' => 'https://example.com/image.jpg'
    ],
    'row_attr' => ['class' => 'mb-3']
])
```

### **2. Contrôleurs améliorés**

#### **CategoryAdminController.php**
- ✅ **Méthode `new()`** : Gère l'upload ET l'URL
- ✅ **Méthode `edit()`** : Gère l'upload ET l'URL
- ✅ **Méthode `handleImageUpload()`** : Upload sécurisé avec validation
- ✅ **Méthode `getFileExtension()`** : Gestion des extensions de fichiers

#### **ProductAdminController.php**
- ✅ **Méthode `new()`** : Gère l'upload ET l'URL pour l'image principale
- ✅ **Méthode `edit()`** : Gère l'upload ET l'URL pour l'image principale
- ✅ **Gestion des images supplémentaires** : Upload multiple
- ✅ **Validation des URLs** : Vérification avec `filter_var()`

### **3. Logique de priorité**

```php
// 1. Priorité à l'upload de fichier
$imageFile = $form->get('imageFile')->getData();
if ($imageFile) {
    $imagePath = $this->handleImageUpload($imageFile, $slugger);
    if ($imagePath) {
        $category->setImage($imagePath);
    }
} else {
    // 2. Fallback sur l'URL si pas d'upload
    $imageUrl = $form->get('image')->getData();
    if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        $category->setImage($imageUrl);
    }
}
```

### **4. Templates avec `enctype="multipart/form-data"`**

#### **Templates de création**
```twig
{{ form_start(form, {'attr': {'enctype': 'multipart/form-data'}}) }}
```

#### **Templates d'édition**
```twig
{{ form_start(form, {'attr': {'enctype': 'multipart/form-data'}}) }}

<!-- Affichage de l'image actuelle -->
{% if category.image %}
    <div class="mb-3">
        <label class="form-label">Image actuelle :</label>
        <div>
            <img src="{{ category.image }}" alt="Image actuelle" class="image-preview" style="max-width: 200px; max-height: 200px;">
        </div>
    </div>
{% endif %}
```

### **5. Affichage des images dans les listes**

#### **Template des catégories**
```twig
<th>Image</th>
<!-- ... -->
<td>
    {% if category.image %}
        <img src="{{ category.image }}" alt="{{ category.name }}" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
    {% else %}
        <span class="text-muted">Aucune image</span>
    {% endif %}
</td>
```

#### **Template des produits**
```twig
<th>Image</th>
<!-- ... -->
<td>
    {% if product.mainImage %}
        <img src="{{ product.mainImage }}" alt="{{ product.name }}" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
    {% else %}
        <span class="text-muted">Aucune image</span>
    {% endif %}
</td>
```

## 🗂️ Structure des répertoires d'upload

```
public/uploads/
├── categories/           # Images des catégories
│   ├── category-1-abc123.jpg
│   └── category-2-def456.png
└── products/
    ├── main/            # Images principales des produits
    │   ├── product-1-ghi789.jpg
    │   └── product-2-jkl012.png
    └── additional/      # Images supplémentaires des produits
        ├── product-1-mno345.jpg
        └── product-1-pqr678.png
```

## 🔒 Sécurité et validation

### **Validation des fichiers**
- ✅ **Types MIME** : Vérification des types d'images
- ✅ **Extensions** : Support JPEG, PNG, GIF, WebP
- ✅ **Taille** : Gestion des erreurs d'upload
- ✅ **Noms sécurisés** : Slugification + uniqid()

### **Validation des URLs**
- ✅ **Format URL** : Validation avec `filter_var()`
- ✅ **Fallback gracieux** : Si URL invalide, pas d'erreur

### **Gestion des erreurs**
- ✅ **Messages utilisateur** : Flash messages explicites
- ✅ **Logs détaillés** : Erreurs techniques pour le debug
- ✅ **Nettoyage** : Suppression des fichiers temporaires en cas d'erreur

## 🎯 Utilisation

### **Pour les administrateurs**

1. **Créer une catégorie avec image** :
   - Aller sur `/admin/categories/new`
   - Choisir : Upload de fichier OU URL d'image
   - Remplir les autres champs
   - Sauvegarder

2. **Créer un produit avec image** :
   - Aller sur `/admin/products/new`
   - Choisir : Upload de fichier OU URL d'image pour l'image principale
   - Optionnel : Ajouter des images supplémentaires
   - Sauvegarder

3. **Modifier une image existante** :
   - Aller sur la page d'édition
   - Voir l'image actuelle
   - Choisir une nouvelle image (upload ou URL)
   - Sauvegarder

### **Pour les utilisateurs**

Les images s'affichent automatiquement :
- ✅ **Liste des produits** : Images au-dessus du titre
- ✅ **Cartes produits** : Images principales visibles
- ✅ **Pages de détail** : Images principales et supplémentaires

## 🚀 Avantages

1. **Flexibilité** : Upload local OU URL externe
2. **Sécurité** : Validation stricte des fichiers et URLs
3. **Performance** : Images optimisées et mises en cache
4. **UX** : Aperçu des images avant sauvegarde
5. **Maintenance** : Gestion automatique des répertoires
6. **Fallback** : Gestion gracieuse des erreurs

## 📝 Notes techniques

- **Base de données** : Colonnes `image` (Category) et `mainImage` (Product) de type VARCHAR(255)
- **Stockage** : Chemins relatifs stockés en base, fichiers dans `/public/uploads/`
- **Formulaires** : `enctype="multipart/form-data"` obligatoire pour l'upload
- **Validation** : Priorité à l'upload, fallback sur l'URL
- **Nettoyage** : Suppression automatique des anciennes images lors de la modification/suppression

Les formulaires sont maintenant entièrement fonctionnels pour l'upload d'images ! 🎉
