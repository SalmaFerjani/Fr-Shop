# 🚨 SOLUTION MANUELLE - Erreur PhpStan Parser

## ❌ Problème identifié
Votre installation **Laragon** a des **extensions PHP manquantes** :
- curl, fileinfo, gd, intl, mbstring, exif, mysqli, openssl, pdo_mysql, xsl

## ✅ SOLUTIONS IMMÉDIATES

### **Solution 1 : Redémarrer Laragon (RECOMMANDÉE)**
1. **Fermer complètement Laragon**
2. **Redémarrer Laragon en tant qu'administrateur**
3. **Vérifier que PHP fonctionne** : `php -v`
4. **Réessayer** : `composer install`

### **Solution 2 : Correction manuelle du problème PhpStan**

#### Étape 1 : Modifier le fichier PhpStanExtractor
```php
// Ouvrir le fichier : vendor/symfony/property-info/Extractor/PhpStanExtractor.php
// Ligne 67, remplacer :
new ConstExprParser()

// Par :
new ConstExprParser(new ParserConfig())
```

#### Étape 2 : Créer un patch temporaire
```php
<?php
// Créer le fichier : fix-phpstan-patch.php
$file = 'vendor/symfony/property-info/Extractor/PhpStanExtractor.php';
$content = file_get_contents($file);
$content = str_replace(
    'new ConstExprParser()',
    'new ConstExprParser(new ParserConfig())',
    $content
);
file_put_contents($file, $content);
echo "Patch appliqué avec succès !\n";
```

### **Solution 3 : Utiliser une version compatible**

#### Modifier composer.json :
```json
{
    "require": {
        "phpstan/phpdoc-parser": "^1.20"
    }
}
```

#### Puis exécuter :
```bash
composer update phpstan/phpdoc-parser
```

## 🔧 CORRECTION LARAGON

### **Problème : Extensions PHP manquantes**

#### **Méthode 1 : Réparer Laragon**
1. Ouvrir **Laragon Control Panel**
2. Cliquer sur **Menu** → **Tools** → **Quick App** → **Repair**
3. Redémarrer Laragon

#### **Méthode 2 : Vérifier php.ini**
1. Ouvrir `D:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.ini`
2. Décommenter les extensions :
```ini
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=exif
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=xsl
```

#### **Méthode 3 : Réinstaller Laragon**
1. Sauvegarder vos projets
2. Désinstaller Laragon
3. Télécharger la dernière version
4. Réinstaller avec les extensions par défaut

## 🚀 SOLUTION RAPIDE (PATCH TEMPORAIRE)

### **Créer le fichier de correction :**
```php
<?php
// fix-phpstan-temp.php
echo "Application du patch temporaire...\n";

$file = 'vendor/symfony/property-info/Extractor/PhpStanExtractor.php';
if (file_exists($file)) {
    $content = file_get_contents($file);
    
    // Remplacer l'ancien constructeur
    $old = 'new ConstExprParser()';
    $new = 'new ConstExprParser(new ParserConfig())';
    
    if (strpos($content, $old) !== false) {
        $content = str_replace($old, $new, $content);
        file_put_contents($file, $content);
        echo "✅ Patch appliqué avec succès !\n";
        echo "L'erreur PhpStan devrait être corrigée.\n";
    } else {
        echo "❌ Le fichier a déjà été modifié ou n'est pas trouvé.\n";
    }
} else {
    echo "❌ Le fichier PhpStanExtractor.php n'existe pas.\n";
    echo "Exécutez d'abord : composer install\n";
}
```

### **Exécuter le patch :**
```bash
php fix-phpstan-temp.php
```

## 🧪 TEST DE LA CORRECTION

### **1. Vérifier que l'application fonctionne :**
```bash
php bin/console cache:clear
php bin/console server:start
```

### **2. Tester la page d'administration :**
- Aller sur `http://localhost:8000/admin/product/new`
- Vérifier qu'il n'y a plus d'erreur 500

### **3. Vérifier les logs :**
```bash
tail -f var/log/dev.log
```

## 📞 SUPPORT URGENT

Si aucune solution ne fonctionne :

### **Option 1 : Utiliser XAMPP**
1. Télécharger XAMPP
2. Copier le projet dans `htdocs`
3. Utiliser la console XAMPP

### **Option 2 : Utiliser Docker**
```bash
# Créer un Dockerfile
FROM php:8.1-fpm
RUN docker-php-ext-install pdo_mysql mbstring intl curl
COPY . /var/www/html
WORKDIR /var/www/html
RUN composer install
```

### **Option 3 : Utiliser WSL2**
1. Installer WSL2 avec Ubuntu
2. Installer PHP 8.1 et Composer
3. Cloner le projet dans WSL2

## ✅ VALIDATION FINALE

Après correction, l'application doit :
- ✅ Se lancer sans erreur
- ✅ Afficher la page d'accueil
- ✅ Permettre l'accès à l'administration
- ✅ Créer/modifier des produits sans erreur 500
- ✅ Aucune erreur dans les logs

## 🎯 PRIORITÉS

1. **IMMÉDIAT** : Appliquer le patch temporaire
2. **COURT TERME** : Réparer Laragon
3. **LONG TERME** : Mettre à jour les dépendances
