document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal');

    if (editModal) {
        // Écouteur pour s'assurer que Flatpickr et la validation sont initialisés à l'ouverture de la modale
        editModal.addEventListener('shown.bs.modal', function () {
            // --- Initialisation de Flatpickr pour la DATE ---
            const dateInput = document.getElementById('nouvelle_date_rdv');
            // La valeur par défaut est déjà dans l'attribut 'value' du champ Twig
            const dateDefaut = dateInput ? dateInput.value : '';

            flatpickr(dateInput, {
                locale: 'fr',
                dateFormat: "Y-m-d", 
                defaultDate: dateDefaut,
                minDate: "today",
                disableMobile: "true" // Empêche le clavier natif sur mobile, utilise Flatpickr
            });

            // --- DEBUT DE LA MODIFICATION : L'initialisation de Flatpickr pour l'HEURE est supprimée ---
            // Le champ d'heure est un <select> HTML normal, pas un <input> qui nécessite Flatpickr.
            // Tenter d'appliquer Flatpickr à un <select> causait une erreur JavaScript qui bloquait l'affichage.
            // Le <select> d'heure est géré nativement par le navigateur et Twig.
            // --- FIN DE LA MODIFICATION ---


            // --- Gestion de la validation du formulaire de modification ---
            const formulaireModification = document.querySelector('#editModal form');
            const boutonMettreAJour = formulaireModification.querySelector('button[type="submit"]');

            const conteneurCheckboxesServices = document.getElementById('services_selection');
            const checkboxesServices = conteneurCheckboxesServices ? conteneurCheckboxesServices.querySelectorAll('input[type="checkbox"]') : [];
            const selectNiveauService = document.getElementById('niveauService_modifier');

            // --- NOUVELLE LOGIQUE DE VALIDATION ---
            function verifierValiditeModificationFormulaire() {
                const estDateSelectionnee = dateInput.value !== '';
                // L'heure est vérifiée via le select, ce qui est correct.
                const heureInput = document.getElementById('nouvelle_heure_rdv'); // Ré-obtenir la référence si nécessaire, bien que déjà disponible
                const estHeureSelectionnee = heureInput.value !== ''; 

                let auMoinsUnServiceCoche = false;
                checkboxesServices.forEach(checkbox => {
                    if (checkbox.checked) {
                        auMoinsUnServiceCoche = true;
                    }
                });

                // Le niveau de service est obligatoire SEULEMENT si au moins un service est coché
                const estNiveauServiceValide = auMoinsUnServiceCoche ? (selectNiveauService.value !== '') : true;

                // Le formulaire est valide si :
                // 1. La date est sélectionnée
                // 2. L'heure est sélectionnée
                // 3. Et la condition de niveau de service est remplie (soit un niveau est choisi ET un service est coché, soit aucun service n'est coché donc le niveau est optionnel)
                const estFormulaireValide = estDateSelectionnee && estHeureSelectionnee && estNiveauServiceValide;

                boutonMettreAJour.disabled = !estFormulaireValide;
            }

            // --- Écouteurs d'événements pour déclencher la validation ---
            dateInput.addEventListener('change', verifierValiditeModificationFormulaire);
            const heureInput = document.getElementById('nouvelle_heure_rdv'); // S'assurer que heureInput est défini ici aussi
            heureInput.addEventListener('change', verifierValiditeModificationFormulaire); 

            checkboxesServices.forEach(checkbox => {
                checkbox.addEventListener('change', verifierValiditeModificationFormulaire);
            });

            selectNiveauService.addEventListener('change', verifierValiditeModificationFormulaire);

            // --- Vérification initiale au chargement de la modale ---
            verifierValiditeModificationFormulaire();
        });
    }
});