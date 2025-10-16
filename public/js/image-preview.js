/**
 * Script pour la prévisualisation des images dans les formulaires
 */

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Supprimer l'ancienne prévisualisation s'il y en a une
            const existingPreview = input.parentNode.querySelector('.image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            // Créer l'élément d'image de prévisualisation
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'image-preview mt-2';
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            img.style.borderRadius = '8px';
            img.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            
            // Insérer l'image après l'input
            input.parentNode.appendChild(img);
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function previewMultipleImages(input) {
    if (input.files && input.files.length > 0) {
        // Supprimer l'ancienne prévisualisation s'il y en a une
        const existingContainer = input.parentNode.querySelector('.image-preview-container');
        if (existingContainer) {
            existingContainer.remove();
        }
        
        // Créer le conteneur pour les images
        const container = document.createElement('div');
        container.className = 'image-preview-container mt-2';
        container.style.display = 'flex';
        container.style.flexWrap = 'wrap';
        container.style.gap = '10px';
        
        // Parcourir tous les fichiers sélectionnés
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'image-preview';
                img.style.maxWidth = '150px';
                img.style.maxHeight = '150px';
                img.style.borderRadius = '8px';
                img.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                img.style.objectFit = 'cover';
                
                container.appendChild(img);
            };
            
            reader.readAsDataURL(file);
        });
        
        // Insérer le conteneur après l'input
        input.parentNode.appendChild(container);
    }
}

// Fonction pour valider les types de fichiers
function validateImageFile(input) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const file = input.files[0];
    
    if (file && !allowedTypes.includes(file.type)) {
        alert('Veuillez sélectionner un fichier image valide (JPG, PNG, GIF, WebP).');
        input.value = '';
        return false;
    }
    
    // Vérifier la taille du fichier (max 5MB)
    if (file && file.size > 5 * 1024 * 1024) {
        alert('Le fichier est trop volumineux. Taille maximum autorisée : 5MB.');
        input.value = '';
        return false;
    }
    
    return true;
}

// Ajouter les événements de validation aux inputs de type file
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (validateImageFile(this)) {
                if (this.hasAttribute('multiple')) {
                    previewMultipleImages(this);
                } else {
                    previewImage(this);
                }
            }
        });
    });
});
