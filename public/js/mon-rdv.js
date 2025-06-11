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
                dateFormat: "d M Y", // Format d'affichage et d'envoi (ex: 11/06/2025)
                defaultDate: dateDefaut,
                minDate: "today",
                disableMobile: "true" // Empêche le clavier natif sur mobile, utilise Flatpickr
            });

            // --- Initialisation de Flatpickr pour l'HEURE ---
            const heureInput = document.getElementById('nouvelle_heure_rdv');
            // La valeur par défaut est déjà dans l'attribut 'value' du champ Twig
            const heureDefaut = heureInput ? heureInput.value : '';

            flatpickr(heureInput, {
                locale: 'fr',
                enableTime: true,   // Active le sélecteur d'heure
                noCalendar: true,   // Cache le calendrier, n'affiche que le sélecteur d'heure
                dateFormat: "H:i",  // Format de l'heure (ex: 14:30)
                time_24hr: true,    // Format 24 heures
                minuteIncrement: 10, // Incrément des minutes par 10
                defaultDate: heureDefaut, // Pré-remplit l'heure actuelle
                // Limite les heures sélectionnables aux options spécifiques
                enable: [
                   { from: "09:00", to: "09:00" },
                   { from: "10:10", to: "10:10" },
                   { from: "11:10", to: "11:10" },
                   { from: "12:10", to: "12:10" },
                   { from: "14:10", to: "14:10" },
                   { from: "15:10", to: "15:10" },
                   { from: "16:10", to: "16:10" },
                   { from: "17:10", to: "17:10" },
                ],
                disableMobile: "true"
            });


            // --- Gestion de la validation du formulaire de modification ---
            const formulaireModification = document.querySelector('#editModal form');
            const boutonMettreAJour = formulaireModification.querySelector('button[type="submit"]');

            const conteneurCheckboxesServices = document.getElementById('services_selection');
            const checkboxesServices = conteneurCheckboxesServices ? conteneurCheckboxesServices.querySelectorAll('input[type="checkbox"]') : [];
            const selectNiveauService = document.getElementById('niveauService_modifier');

            // --- NOUVELLE LOGIQUE DE VALIDATION ---
            function verifierValiditeModificationFormulaire() {
                const estDateSelectionnee = dateInput.value !== '';
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