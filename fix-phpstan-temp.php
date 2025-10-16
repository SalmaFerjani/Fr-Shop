<?php
// fix-phpstan-temp.php - Patch temporaire pour corriger l'erreur PhpStan
echo "🔧 Application du patch temporaire pour PhpStan...\n";

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
        echo "\n📋 Prochaines étapes :\n";
        echo "1. Redémarrer votre serveur web\n";
        echo "2. Tester l'administration : /admin/product/new\n";
        echo "3. Vérifier qu'il n'y a plus d'erreur 500\n";
    } else {
        echo "❌ Le fichier a déjà été modifié ou n'est pas trouvé.\n";
        echo "Recherche alternative...\n";
        
        // Recherche alternative
        $patterns = [
            'new ConstExprParser(',
            'ConstExprParser()',
            'new \\PHPStan\\PhpDocParser\\Parser\\ConstExprParser()'
        ];
        
        foreach ($patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "Trouvé : $pattern\n";
                $newPattern = str_replace('()', '(new ParserConfig())', $pattern);
                $content = str_replace($pattern, $newPattern, $content);
                file_put_contents($file, $content);
                echo "✅ Patch alternatif appliqué !\n";
                break;
            }
        }
    }
} else {
    echo "❌ Le fichier PhpStanExtractor.php n'existe pas.\n";
    echo "Exécutez d'abord : composer install\n";
    echo "\n🔍 Fichiers trouvés dans vendor/symfony/property-info/ :\n";
    $dir = 'vendor/symfony/property-info/';
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if (strpos($file, '.php') !== false) {
                echo "- $file\n";
            }
        }
    }
}
