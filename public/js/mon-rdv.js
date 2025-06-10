document.addEventListener('DOMContentLoaded', function() {
    // Flatpickr pour la date de modification
    flatpickr("#nouvelle_date_rdv", {
        locale: 'fr', // Utilise la localisation française
        dateFormat: "Y-m-d", // Format interne pour le serveur (Année-Mois-Jour)
        altInput: true, // Affiche un champ plus lisible
        altFormat: "d F Y", // Format affiché à l'utilisateur (Jour Mois Année)
        minDate: "today" // Empêche de choisir des dates passées
    });

    // Flatpickr pour l'heure de modification
    flatpickr("#nouvelle_heure_rdv", {
        locale: 'fr', // Utilise la localisation française
        enableTime: true, // Active le sélecteur d'heure
        noCalendar: true, // Cache le calendrier, n'affiche que le sélecteur d'heure
        dateFormat: "H:i", // Format de l'heure (Heure:Minute)
        time_24hr: true, // Format 24 heures
        minuteIncrement: 10, // Incrément des minutes par 10
        minTime: "09:00", // Heure minimale
        maxTime: "17:30", // Heure maximale
        // Plages horaires spécifiques comme dans votre exemple de "prendre rendez-vous"
        enable: [
           { from: "09:00", to: "09:00" },
           { from: "10:10", to: "10:10" },
           { from: "11:10", to: "11:10" },
           { from: "12:10", to: "12:10" },
           { from: "14:10", to: "14:10" },
           { from: "15:10", to: "15:10" },
           { from: "16:10", to: "16:10" },
           { from: "17:10", to: "17:10" },
        ]
    });


// PARTIE AVIS CLIENT :  // js/mon-rdv.js

    const ratingStarsContainer = $('.rating-stars');

    ratingStarsContainer.on('click', '.star-label', function() {
        const clickedRating = $(this).data('rating'); // Récupère la note de l'étoile cliquée

        // 1. Gérer l'état visuel des étoiles
        // Supprime la classe 'selected' de toutes les étoiles dans ce conteneur
        $(this).parent().find('.star-label').removeClass('selected');

        // Ajoute la classe 'selected' aux étoiles jusqu'à celle qui a été cliquée
        // .prevAll() sélectionne tous les frères précédents
        // .addBack() inclut l'élément actuel (l'étoile cliquée)
        $(this).prevAll('.star-label').addBack().addClass('selected');

        // 2. Cocher le bouton radio correspondant
        // Trouver le radio bouton avec l'ID correspondant à la note cliquée
        $('#star_avis_' + clickedRating).prop('checked', true);
    });

    // Optionnel : Gérer l'état des étoiles au chargement de la page si une note est déjà sélectionnée (ex: après une erreur de validation)
    // Cela vérifie si un radio est déjà coché et met à jour l'affichage des étoiles
    ratingStarsContainer.find('input[type="radio"][name="note"]:checked').each(function() {
        const initialRating = $(this).val();
        ratingStarsContainer.find('.star-label').removeClass('selected');
        ratingStarsContainer.find('.star-label[data-rating="' + initialRating + '"]').prevAll('.star-label').addBack().addClass('selected');
    });
});
});