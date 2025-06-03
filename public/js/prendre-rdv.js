// document.addEventListener('DOMContentLoaded', () => {
//     const calendrierJoursElement = document.getElementById('calendrier-jours');
//     const moisEnCoursElement = document.getElementById('mois-en-cours');
//     const anneeSelectElement = document.getElementById('annee');
//     const boutonMoisPrecedent = document.getElementById('mois-precedent');
//     const boutonMoisSuivant = document.getElementById('mois-suivant');
//     const boutonValider = document.getElementById('validerBtn');
//     const listeHeuresElement = document.getElementById('liste-heures');
//     const formulaireRdv = document.getElementById('rdvForm');
//     const champDateCache = document.getElementById('input-date');
//     const champHeureCache = document.getElementById('input-heure');
//     const selectService = document.getElementById('service');
//     const selectTypeService = document.getElementById('typeService');

//     const nomsDesMois = [
//         'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
//         'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
//     ];

//     let moisActuel = new Date().getMonth();
//     let anneeActuelle = new Date().getFullYear();
//     let heureSelectionnee = null;

//     function verifierValiditeFormulaire() {
//         const estJourSelectionne = champDateCache.value !== '';
//         const estHeureSelectionnee = heureSelectionnee !== null;
//         const estServiceSelectionne = selectService.value !== '';
//         const estTypeServiceSelectionne = selectTypeService.value !== '';
//         boutonValider.disabled = !(estJourSelectionne && estHeureSelectionnee && estServiceSelectionne && estTypeServiceSelectionne);
//     }

//     function afficherLesJours(mois, annee) {
//         calendrierJoursElement.innerHTML = '';
//         const nombreDeJours = new Date(annee, mois + 1, 0).getDate();
//         for (let i = 1; i <= nombreDeJours; i++) {
//             const boutonJour = document.createElement('button');
//             boutonJour.type = 'button';
//             boutonJour.className = 'btn btn-outline-success rounded-md px-3 py-2 text-sm font-semibold text-center';
//             boutonJour.textContent = i;
//             boutonJour.addEventListener('click', function() {
//                 calendrierJoursElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
//                 this.classList.add('active');
//                 const jour = this.textContent;
//                 const moisFormat = (moisActuel + 1).toString().padStart(2, '0');
//                 const anneeFormat = anneeActuelle;
//                 const dateFormatee = `${anneeFormat}-${moisFormat}-${jour.toString().padStart(2, '0')}`;
//                 champDateCache.value = dateFormatee;
//                 verifierValiditeFormulaire();
//             });
//             calendrierJoursElement.appendChild(boutonJour);
//         }
//     }

//     function mettreAJourLeMois() {
//         moisEnCoursElement.textContent = nomsDesMois[moisActuel];
//         anneeSelectElement.value = anneeActuelle;
//         afficherLesJours(moisActuel, anneeActuelle);
//         champDateCache.value = '';
//     }

//     if (boutonMoisPrecedent) {
//         boutonMoisPrecedent.addEventListener('click', () => {
//             moisActuel--;
//             if (moisActuel < 0) {
//                 moisActuel = 11;
//                 anneeActuelle--;
//             }
//             mettreAJourLeMois();
//         });
//     }

//     if (boutonMoisSuivant) {
//         boutonMoisSuivant.addEventListener('click', () => {
//             moisActuel++;
//             if (moisActuel > 11) {
//                 moisActuel = 0;
//                 anneeActuelle++;
//             }
//             mettreAJourLeMois();
//         });
//     }

//     anneeSelectElement.addEventListener('change', () => {
//         anneeActuelle = parseInt(anneeSelectElement.value);
//         mettreAJourLeMois();
//     });

//     listeHeuresElement.querySelectorAll('button').forEach(boutonHeure => {
//         boutonHeure.addEventListener('click', () => {
//             listeHeuresElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
//             boutonHeure.classList.add('active');
//             heureSelectionnee = boutonHeure.textContent;
//             champHeureCache.value = heureSelectionnee;
//             verifierValiditeFormulaire();
//         });
//     });

//     selectService.addEventListener('change', () => {
//         verifierValiditeFormulaire();
//     });

//     selectTypeService.addEventListener('change', () => {
//         verifierValiditeFormulaire();
//     });

//     formulaireRdv.addEventListener('submit', (evenement) => {
//         const dateSelectionnee = champDateCache.value;
//         const heureSelectionneePourSubmit = champHeureCache.value;
//         const serviceSelectionne = selectService.value;
//         const typeServiceSelectionne = selectTypeService.value;

//         if (!dateSelectionnee || !heureSelectionneePourSubmit || serviceSelectionne === '' || typeServiceSelectionne === '') {
//             alert('Veuillez sélectionner un jour, une heure, un service et un type de service.');
//             evenement.preventDefault(); // Empêche la soumission si non valide
//             return;
//         }
//         // Si tous les champs sont remplis, laissez la soumission se produire normalement.
//     });

//     mettreAJourLeMois();
//     verifierValiditeFormulaire();

//     const anneeCourante = new Date().getFullYear();
//     for (let i = anneeCourante; i <= anneeCourante + 5; i++) {
//         const option = document.createElement('option');
//         option.value = i;
//         option.textContent = i;
//         anneeSelectElement.appendChild(option);
//     }
//     anneeSelectElement.value = anneeCourante;
// });