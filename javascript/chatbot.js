// Sélectionne les éléments nécessaires
const profileIcon = document.querySelector('.profile-link'); // L'icône du profil
const dropdownMenu = document.querySelector('.dropdown'); // Le menu déroulant

// Ajoute un écouteur d'événement au clic sur l'icône de profil
profileIcon.addEventListener('click', function(event) {
    event.stopPropagation(); // Empêche la propagation du clic à d'autres éléments
    // Si le menu est déjà affiché, on le cache, sinon on l'affiche
    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
});

// Ferme le menu si on clique ailleurs
document.addEventListener('click', function(event) {
    if (!profileIcon.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.style.display = 'none'; // Cache le menu si le clic est en dehors de l'icône ou du menu
    }
});