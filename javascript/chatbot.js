// Récupérer l'élément du textarea
const textarea = document.getElementById('user-input');

// Ajouter un écouteur d'événements pour la saisie
textarea.addEventListener('input', function () {
    // Réinitialiser la hauteur du textarea à 'auto' avant de calculer la nouvelle hauteur
    this.style.height = 'auto';
    
    // Ajuster la hauteur en fonction du contenu tout en respectant la hauteur maximale
    if (this.scrollHeight < 200) {
        this.style.height = this.scrollHeight + 'px';
    } else {
        this.style.height = '200px'; // Hauteur maximale de 200px
    }
});