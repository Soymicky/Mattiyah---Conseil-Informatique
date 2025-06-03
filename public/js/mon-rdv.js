document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            // Lorsque la modale est affichée, initialiser le calendrier ici
            const nouveauCalendrierJoursElement = document.getElementById('nouveau-calendrier-jours');
            const nouveauMoisEnCoursElement = document.getElementById('nouveau-mois-en-cours');
            const nouveauAnneeSelectElement = document.getElementById('nouveau-annee');
            const nouveauBoutonMoisPrecedent = document.getElementById('nouveau-mois-precedent');
            const nouveauBoutonMoisSuivant = document.getElementById('nouveau-mois-suivant');
            const nouvelleInputDateElement = document.getElementById('nouvelle-input-date');

            const nomsDesMois = [
                'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
            ];

            let nouveauMoisActuel = new Date().getMonth();
            let nouveauAnneeActuel = new Date().getFullYear();

            function afficherNouveauxJours(mois, annee) {
                nouveauCalendrierJoursElement.innerHTML = '';
                const nombreDeJours = new Date(annee, mois + 1, 0).getDate();
                for (let i = 1; i <= nombreDeJours; i++) {
                    const boutonJour = document.createElement('button');
                    boutonJour.type = 'button';
                    boutonJour.className = 'btn btn-outline-success rounded-md px-3 py-2 text-sm font-semibold text-center';
                    boutonJour.textContent = i;
                    boutonJour.addEventListener('click', function() {
                        nouveauCalendrierJoursElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        const jour = this.textContent;
                        const moisFormat = (nouveauMoisActuel + 1).toString().padStart(2, '0');
                        const anneeFormat = nouveauAnneeActuel;
                        const dateFormatee = `${anneeFormat}-${moisFormat}-${jour.toString().padStart(2, '0')}`;
                        nouvelleInputDateElement.value = dateFormatee;
                    });
                    nouveauCalendrierJoursElement.appendChild(boutonJour);
                }
            }

            function mettreAJourNouveauMois() {
                nouveauMoisEnCoursElement.textContent = nomsDesMois[nouveauMoisActuel];
                nouveauAnneeSelectElement.value = nouveauAnneeActuel;
                afficherNouveauxJours(nouveauMoisActuel, nouveauAnneeActuel);
            }

            nouveauBoutonMoisPrecedent.addEventListener('click', () => {
                nouveauMoisActuel--;
                if (nouveauMoisActuel < 0) {
                    nouveauMoisActuel = 11;
                    nouveauAnneeActuel--;
                }
                mettreAJourNouveauMois();
            });

            nouveauBoutonMoisSuivant.addEventListener('click', () => {
                nouveauMoisActuel++;
                if (nouveauMoisActuel > 11) {
                    nouveauMoisActuel = 0;
                    nouveauAnneeActuel++;
                }
                mettreAJourNouveauMois();
            });

            nouveauAnneeSelectElement.addEventListener('change', () => {
                nouveauAnneeActuel = parseInt(nouveauAnneeSelectElement.value);
                mettreAJourNouveauMois();
            });

            // Initialisation du calendrier dans la modale avec la date actuelle du rendez-vous (si elle existe)
            const dateRDV = nouvelleInputDateElement.value;
            if (dateRDV) {
                const [anneeInit, moisInit, jourInit] = dateRDV.split('-');
                nouveauMoisActuel = parseInt(moisInit) - 1;
                nouveauAnneeActuel = parseInt(anneeInit);
            }
            mettreAJourNouveauMois();

            // Initialisation des années dans le select de la modale
            const anneeCourante = new Date().getFullYear();
            for (let i = anneeCourante - 5; i <= anneeCourante + 5; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = i;
                nouveauAnneeSelectElement.appendChild(option);
            }
            if (dateRDV) {
                nouveauAnneeSelectElement.value = parseInt(dateRDV.split('-')[0]);
            } else {
                nouveauAnneeSelectElement.value = new Date().getFullYear();
            }
        });
    }
});