# 🔧 Guide de Correction - Erreur PhpStan Parser

## 🚨 Problème identifié
**Erreur :** `ArgumentCountError` dans `PhpStanExtractor.php`
**Cause :** Conflit de versions entre `phpstan/phpdoc-parser` 2.2.0 et `symfony/property-info` 6.1

## ✅ Solutions disponibles

### **Solution 1 : Downgrade de phpstan/phpdoc-parser (RECOMMANDÉE)**

1. **Modifier composer.json :**
   ```json
   "phpstan/phpdoc-parser": "^1.20"
   ```

2. **Exécuter les commandes :**
   ```bash
   # Supprimer le cache et les dépendances
   rm -rf vendor/
   rm composer.lock
   
   # Réinstaller avec la version corrigée
   composer install --no-dev --optimize-autoloader
   
   # Nettoyer le cache Symfony
   php bin/console cache:clear
   ```

### **Solution 2 : Mise à jour de Symfony vers 6.4**

1. **Remplacer composer.json par :**
   ```json
   "symfony/asset": "6.4.*",
   "symfony/console": "6.4.*",
   // ... toutes les autres dépendances Symfony en 6.4.*
   ```

2. **Exécuter :**
   ```bash
   composer update symfony/*
   ```

### **Solution 3 : Configuration de résolution de conflit**

1. **Ajouter dans composer.json :**
   ```json
   "resolutions": {
       "phpstan/phpdoc-parser": "^1.20"
   }
   ```

2. **Exécuter :**
   ```bash
   composer update
   ```

## 🛠️ Scripts de correction automatique

### Windows (fix-phpstan-error.bat)
```batch
@echo off
echo Correction de l'erreur PhpStan Parser
copy composer.json composer.json.backup
composer clear-cache
rmdir /s /q vendor
del composer.lock
composer install --no-dev --optimize-autoloader
php bin/console cache:clear
echo Correction terminee !
pause
```

### Linux/Mac (fix-phpstan-error.sh)
```bash
#!/bin/bash
echo "Correction de l'erreur PhpStan Parser"
cp composer.json composer.json.backup
composer clear-cache
rm -rf vendor/
rm composer.lock
composer install --no-dev --optimize-autoloader
php bin/console cache:clear
echo "Correction terminee !"
```

## 🔍 Vérification de la correction

1. **Tester l'application :**
   ```bash
   php bin/console server:start
   # Ou
   symfony serve
   ```

2. **Vérifier les logs :**
   ```bash
   tail -f var/log/dev.log
   ```

3. **Tester la page d'administration :**
   - Aller sur `/admin/category/new`
   - Vérifier qu'il n'y a plus d'erreur 500

## 🚨 Problèmes Laragon détectés

Votre installation Laragon a des problèmes avec les extensions PHP :
- curl, fileinfo, gd, intl, mbstring, exif, mysqli, openssl, pdo_mysql, xsl

### Correction Laragon :
1. **Redémarrer Laragon** complètement
2. **Vérifier la configuration PHP** dans `php.ini`
3. **Réinstaller les extensions manquantes**

## 📞 Support

Si le problème persiste :
1. Vérifier la version PHP : `php -v`
2. Vérifier les extensions : `php -m`
3. Consulter les logs : `var/log/dev.log`
4. Tester avec une installation PHP propre

## ✅ Test de validation

Après correction, l'application doit :
- ✅ Se lancer sans erreur
- ✅ Afficher la page d'accueil
- ✅ Permettre l'accès à l'administration
- ✅ Créer/modifier des catégories sans erreur 500

