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
});