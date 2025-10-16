# Guide de Fonctionnalité d'Upload - La Boutique Française

## ✅ Fonctionnalités d'upload implémentées

Les formulaires de catégories et de produits permettent maintenant aux utilisateurs d'uploader des images depuis leur PC avec une gestion complète et sécurisée.

## 🔧 Configuration technique

### **1. Entités de base de données**

#### **Category Entity**
```php
#[ORM\Column(length: 255, nullable: true)]
private ?string $image = null;
```

#### **Product Entity**
```php
#[ORM\Column(length: 255, nullable: true)]
private ?string $mainImage = null;

#[ORM\Column(type: 'json', nullable: true)]
private array $images = [];
```

### **2. Formulaires avec champs file**

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
->add('additionalImages', FileType::class, [
    'label' => 'Images supplémentaires',
    'mapped' => false,
    'required' => false,
    'multiple' => true,
    'attr' => [
        'class' => 'form-control',
        'accept' => 'image/*',
        'multiple' => true,
        'onchange' => 'previewMultipleImages(this)'
    ],
    'row_attr' => ['class' => 'mb-3']
])
```

### **3. Templates avec enctype="multipart/form-data"**

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
```php
// Vérifier que le fichier est valide
if (!$file->isValid()) {
    $this->addFlash('error', 'Fichier invalide : ' . $file->getErrorMessage());
    return null;
}

// Vérifier la taille du fichier (max 5MB)
if ($file->getSize() > 5 * 1024 * 1024) {
    $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximale : 5MB');
    return null;
}

// Vérifier le type MIME
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
    $this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : JPEG, PNG, GIF, WebP');
    return null;
}
```

### **Gestion des erreurs**
```php
// Vérifier que le fichier n'est pas vide
if (filesize($fullPath) === 0) {
    unlink($fullPath);
    $this->addFlash('error', 'Le fichier uploadé est vide.');
    return null;
}
```

### **Configuration .htaccess**
```apache
# Configuration pour les uploads d'images
<IfModule mod_rewrite.c>
    RewriteEngine On
</IfModule>

# Autoriser l'accès aux fichiers d'images
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Désactiver l'exécution de scripts dans le répertoire uploads
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
```

## 🎯 Fonctionnalités d'upload

### **1. Upload d'images pour les catégories**

#### **Processus d'upload**
1. **Sélection du fichier** : L'utilisateur sélectionne une image depuis son PC
2. **Validation** : Vérification du type MIME, de la taille et de l'extension
3. **Sauvegarde** : Le fichier est déplacé vers `/public/uploads/categories/`
4. **Enregistrement** : Le chemin relatif est stocké dans la base de données
5. **Affichage** : L'image est affichée dans les cartes des catégories

#### **Code de gestion**
```php
// Gestion de l'upload de l'image
$imageFile = $form->get('imageFile')->getData();
if ($imageFile) {
    $imagePath = $this->handleImageUpload($imageFile, $slugger);
    if ($imagePath) {
        $category->setImage($imagePath);
    } else {
        $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
    }
}
```

### **2. Upload d'images pour les produits**

#### **Images principales**
- **Champ** : `mainImageFile`
- **Répertoire** : `/public/uploads/products/main/`
- **Base de données** : Colonne `mainImage` (VARCHAR 255)

#### **Images supplémentaires**
- **Champ** : `additionalImages` (multiple)
- **Répertoire** : `/public/uploads/products/additional/`
- **Base de données** : Colonne `images` (JSON)

#### **Code de gestion**
```php
// Gestion de l'upload de l'image principale
$mainImageFile = $form->get('mainImageFile')->getData();
if ($mainImageFile) {
    $mainImagePath = $this->handleImageUpload($mainImageFile, $slugger, 'main');
    if ($mainImagePath) {
        $product->setMainImage($mainImagePath);
    }
}

// Gestion des images supplémentaires
$additionalImages = $form->get('additionalImages')->getData();
if ($additionalImages) {
    $imagePaths = [];
    foreach ($additionalImages as $imageFile) {
        $imagePath = $this->handleImageUpload($imageFile, $slugger, 'additional');
        if ($imagePath) {
            $imagePaths[] = $imagePath;
        }
    }
    if (!empty($imagePaths)) {
        $product->setImages($imagePaths);
    }
}
```

## 🎨 Affichage des images

### **Cartes des catégories**
```twig
{% if category.image %}
    <div class="card-img-top" style="height: 250px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
        <img src="{{ category.image }}" alt="{{ category.name }}" style="width: 100%; height: 100%; object-fit: cover;">
    </div>
{% else %}
    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
        <i class="fas fa-tags text-muted" style="font-size: 3rem;"></i>
    </div>
{% endif %}
```

### **Cartes des produits**
```twig
{% if product.mainImage %}
    <img src="{{ product.mainImage }}" class="card-img-top" alt="{{ product.name }}" loading="lazy" width="600" height="400">
{% else %}
    <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
        <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
    </div>
{% endif %}
```

## 🚀 Utilisation

### **Pour les administrateurs**

1. **Créer une catégorie avec image** :
   - Aller sur `/admin/categories/new`
   - Remplir le nom et la description
   - Sélectionner une image depuis le PC
   - Sauvegarder

2. **Créer un produit avec images** :
   - Aller sur `/admin/products/new`
   - Remplir les informations du produit
   - Sélectionner une image principale
   - Optionnel : Ajouter des images supplémentaires
   - Sauvegarder

3. **Modifier une image existante** :
   - Aller sur la page d'édition
   - Voir l'image actuelle
   - Sélectionner une nouvelle image
   - Sauvegarder

### **Pour les utilisateurs**

Les images s'affichent automatiquement :
- ✅ **Liste des catégories** : Images harmonisées avec taille fixe
- ✅ **Liste des produits** : Images principales visibles
- ✅ **Pages de détail** : Images principales et supplémentaires

## 📝 Notes techniques

- **Base de données** : Colonnes `image` (Category) et `mainImage` (Product) de type VARCHAR(255)
- **Stockage** : Chemins relatifs stockés en base, fichiers dans `/public/uploads/`
- **Formulaires** : `enctype="multipart/form-data"` obligatoire pour l'upload
- **Validation** : Types MIME, taille, extension, contenu
- **Sécurité** : Noms sécurisés, permissions, exécution désactivée
- **Affichage** : Images harmonisées avec `object-fit: cover`

L'upload d'images est maintenant entièrement fonctionnel ! 🎉
