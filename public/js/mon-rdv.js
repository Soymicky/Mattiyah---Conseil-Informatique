document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editModal'); 

    if (editModal) { 
        editModal.addEventListener('shown.bs.modal', function () {
            const dateInput = document.getElementById('nouvelle_date_rdv'); 
            if (dateInput) {
                flatpickr(dateInput, {
                    locale: 'fr', 
                    dateFormat: "Y-m-d", 
                    defaultDate: dateInput.value, 
                    minDate: "today", 
                    disableMobile: "true"
                });
            }

            const formulaireModification = document.querySelector('#editModal form');
            const boutonMettreAJour = formulaireModification.querySelector('button[type="submit"]');
            const checkboxesServices = document.querySelectorAll('#services_selection input[type="checkbox"]');
            const selectNiveauService = document.getElementById('niveauService_modifier');
            const heureInput = document.getElementById('nouvelle_heure_rdv');

            function verifierValiditeModificationFormulaire() {
                const estDateSelectionnee = dateInput.value !== '';
                const estHeureSelectionnee = heureInput.value !== '';

                let auMoinsUnServiceCoche = false;
                checkboxesServices.forEach(checkbox => {
                    if (checkbox.checked) {
                        auMoinsUnServiceCoche = true;
                    }
                });

                const estNiveauServiceValide = auMoinsUnServiceCoche ? (selectNiveauService.value !== '') : true;

                boutonMettreAJour.disabled = !(estDateSelectionnee && estHeureSelectionnee && estNiveauServiceValide);
            }

            if (dateInput) dateInput.addEventListener('change', verifierValiditeModificationFormulaire);
            if (heureInput) heureInput.addEventListener('change', verifierValiditeModificationFormulaire);
            checkboxesServices.forEach(checkbox => {
                checkbox.addEventListener('change', verifierValiditeModificationFormulaire);
            });
            if (selectNiveauService) selectNiveauService.addEventListener('change', verifierValiditeModificationFormulaire);

            verifierValiditeModificationFormulaire();
        });
    }

    const btnAnnuler = document.getElementById('btnAfficherFormAnnulation');         
    const sectionForm = document.getElementById('sectionFormAnnulation');             
    const formConfirm = document.getElementById('formAnnulerRendezVous');             

    if (btnAnnuler && sectionForm) { 
        btnAnnuler.addEventListener('click', () => { 
            sectionForm.classList.remove('d-none'); 
            sectionForm.classList.add('d-block');    
        });
    }

    if (formConfirm) { 
        formConfirm.addEventListener('submit', (event) => { 
            if (!confirm("Êtes-vous sûr de vouloir annuler votre rendez-vous ? Cette action est irréversible.")) {
                event.preventDefault(); 
            }
        });
    }
});