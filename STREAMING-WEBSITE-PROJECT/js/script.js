// Script pour interactions dynamiques
// Switcher de langue : les liens dans la navbar changent l'URL avec ?lang=

// Pour l'avenir : ajouter des confirmations avant soumission, etc.

// Exemple : confirmer ajout
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Pour les tests, pas de confirmation, mais on peut ajouter
            // if (!confirm('Confirmer l\'ajout ?')) {
            //     e.preventDefault();
            // }
        });
    });
});
