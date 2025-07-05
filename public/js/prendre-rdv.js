document.addEventListener('DOMContentLoaded', () => {
    // Sélection des éléments du formulaire
    const boutonValider = document.getElementById('validerBtn');
    const formulaireRdv = document.getElementById('rdvForm');
    const inputDate = document.getElementById('date_rdv');   // Champ de la date
    const inputHeure = document.getElementById('heure_rdv'); // Champ de l'heure
    const selectService = document.getElementById('service'); // Conteneur des checkboxes de services
    const selectNiveauService = document.getElementById('niveauService'); // Select du niveau de service

    // 1. Initialisation de Flatpickr pour la date
    if (inputDate) {
        flatpickr(inputDate, {
            locale: 'fr', // Utilise la localisation française
            dateFormat: "d-m-Y", // Format de date attendu (ex: 2025-12-31)
            minDate: "today", // Empêche de sélectionner des dates passées
            altInput: true, //ecriture alternative en lettre
            altFormat: "d F Y",
        });
    }

    // 2. Initialisation de Flatpickr pour l'heure
    if (inputHeure) {
        flatpickr(inputHeure, {
            locale: 'fr', // Utilise la localisation française
            enableTime: true, // Active le sélecteur d'heure
            noCalendar: true, // Cache le calendrier, n'affiche que le sélecteur d'heure
            dateFormat: "H:i", // Format de l'heure (ex: 14:30)
            time_24hr: true, // Format 24 heures
            minuteIncrement: 10, // Incrément des minutes par 10 (comme dans votre ancien calendrier)
            minTime: "09:00", 
            maxTime: "17:30", 
            
             enable: [
               { from: "09:00", to: "09:00" }, { from: "10:10", to: "10:10" },
               { from: "11:10", to: "11:10" }, { from: "12:10", to: "12:10" },
               { from: "14:10", to: "14:10" }, { from: "15:10", to: "15:10" },
               { from: "16:10", to: "16:10" }, { from: "17:10", to: "17:10" },
             ]
        });
    }


    // 3. Fonction de validation du formulaire (adaptée aux nouveaux champs)
    function verifierValiditeFormulaire() {
        const estJourSelectionne = inputDate.value !== '';
        const estHeureSelectionnee = inputHeure.value !== '';
        
        let estServiceSelectionne = false;
        // La logique de validation des services est maintenue pour les checkboxes
        const checkboxesServices = document.querySelectorAll('#service input[type="checkbox"]:checked');
        estServiceSelectionne = checkboxesServices.length > 0;

        const estNiveauServiceSelectionne = selectNiveauService.value !== '';

        boutonValider.disabled = !(estJourSelectionne && estHeureSelectionnee && estServiceSelectionne && estNiveauServiceSelectionne);
    }
    
    // Les événements 'change' des inputs de date/heure seront déclenchés par Flatpickr
    inputDate.addEventListener('change', verifierValiditeFormulaire);
    inputHeure.addEventListener('change', verifierValiditeFormulaire);
    
    // Écouteur pour chaque checkbox de service
    if (selectService) { // Vérifie si l'élément existe
        selectService.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', verifierValiditeFormulaire);
        });
    }

    selectNiveauService.addEventListener('change', verifierValiditeFormulaire);

    // 5. Écouteur pour la soumission du formulaire
    formulaireRdv.addEventListener('submit', (evenement) => {
        verifierValiditeFormulaire(); // Re-vérifie la validité juste avant la soumission

        if (boutonValider.disabled) {
            alert('Veuillez remplir tous les champs requis avant de valider votre rendez-vous.');
            evenement.preventDefault(); // Empêche la soumission si invalide
            return;
        }
    });

    // 6. Vérification initiale au chargement de la page
    verifierValiditeFormulaire();
});