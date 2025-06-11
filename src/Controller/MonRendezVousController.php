<?php
// src/Controller/MonRendezVousController.php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Services; // Garde Services car c'est le nom de l'entité
use App\Entity\NiveauService;
use App\Entity\RendezVousService;
use App\Repository\NiveauServiceRepository;
use App\Entity\AvisClient;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// Aucun use statement supplémentaire n'est ajouté ici.

final class MonRendezVousController extends AbstractController
{
    #[Route('mon_rendez_vous', name: 'mon_rendez_vous')]
    public function index(
        EntityManagerInterface $entityManager,
        NiveauServiceRepository $niveauServiceRepository,
        ServicesRepository $servicesRepository // Ajouté pour passer 'services' au template
    ): Response {
        
        $utilisateur = $this->getUser();
        $rendezvous = null; // Initialisez à null par défaut

        if ($utilisateur) {
            // Cherche le rendez-vous de l'utilisateur connecté.
            // Ceci est utilisé pour l'affichage de la page de rendez-vous.
            $rendezvous = $entityManager->getRepository(RendezVous::class)
                ->findOneBy(
                    ['utilisateur' => $utilisateur],
                ['dateRDV' => 'DESC'] // Tri par date décroissante
            );
        }

        // Récupère tous les services et niveaux de service pour les afficher dans les formulaires (rendez-vous et avis)
        $services = $servicesRepository->findAll(); // Récupère tous les services
        
        // On passe la variable 'niveauService' (au singulier) au template
        $niveauService = $niveauServiceRepository->findAll();
        

        return $this->render('/back/mon_rendez_vous.html.twig', [
            'rendezvous' => $rendezvous, // Passé pour l'affichage du rendez-vous
            'services' => $services,     // Passé pour la liste des services
            'niveauService' => $niveauService, // C'est bien 'niveauService' (singulier)
        ]);
    }

    #[Route('/modifier_mon_rdv/{id}', name: 'modifier_mon_rdv', methods: ['POST'])]
    public function modifierRendezVous(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ServicesRepository $servicesRepository,
        NiveauServiceRepository $niveauServiceRepository
    ): Response {
        $rendezvous = $entityManager->getRepository(RendezVous::class)->find($id);

        // Vérification de l'existence du rendez-vous et de l'autorisation de l'utilisateur
        if (!$rendezvous || $rendezvous->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('danger', 'Rendez-vous non trouvé ou non autorisé.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        try {
            // --- 1. MISE À JOUR DATE ET HEURE (toujours mises à jour) ---
            $nouvelleDate = $request->request->get('nouvelle_date'); 
            $nouvelleHeure = $request->request->get('heure');     

            // Vérifie que date et heure sont bien présentes avant de créer l'objet DateTime
            if (!$nouvelleDate || !$nouvelleHeure) {
                 $this->addFlash('danger', 'La date et l\'heure sont obligatoires pour la modification.');
                 return $this->redirectToRoute('mon_rendez_vous');
            }

            // MODIFICATION ICI : 'd/m/Y' devient 'd F Y' pour correspondre au format de Flatpickr
            $dateRDV = \DateTime::createFromFormat('d F Y H:i', "$nouvelleDate $nouvelleHeure");
            if (!$dateRDV) {
                $this->addFlash('danger', 'Le format de la date ou de l\'heure n\'est pas valide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
            $rendezvous->setDateRDV($dateRDV);


            // --- 2. DÉTERMINATION DU NIVEAU DE SERVICE À UTILISER (pour les relations RendezVousService) ---
            // CHANGEMENT ICI : 'niveauService' (singulier)
            $submittedNiveauServiceId = $request->request->get('niveauService');
            $niveauServiceToUse = null;

            if ($submittedNiveauServiceId && $submittedNiveauServiceId !== '') { // Si une option non vide a été sélectionnée
                $niveauServiceToUse = $niveauServiceRepository->find($submittedNiveauServiceId);
            } else {
                // Si aucune sélection explicite (ou valeur vide), on tente de garder l'ancien niveau
                // en prenant le niveau du premier RendezVousService déjà associé au rendez-vous.
                $currentRendezVousService = $rendezvous->getRendezVousServices()->first();
                if ($currentRendezVousService) {
                    $niveauServiceToUse = $currentRendezVousService->getNiveauService();
                }
            }

            // Vérification si un niveau de service est finalement disponible pour les nouvelles relations RendezVousService
            // C'est critique si NiveauService est obligatoire sur RendezVousService.
            if (!$niveauServiceToUse) {
                $this->addFlash('danger', 'Un niveau de service doit être défini pour les services sélectionnés.');
                return $this->redirectToRoute('mon_rendez_vous');
            }


            // --- 3. MISE À JOUR DES SERVICES (si l'utilisateur a interagi avec les checkboxes 'service[]') ---
            // CHANGEMENT ICI : 'service' (singulier)
            $submittedServices = $request->request->all('service'); // Utilise all() pour récupérer un tableau même si vide

            if ($submittedServices !== null) { // Si le paramètre 'service' est dans la requête POST, l'utilisateur a interagi.
                // Supprime toutes les relations RendezVousService existantes pour ce rendez-vous
                foreach ($rendezvous->getRendezVousServices() as $rdvService) {
                    $entityManager->remove($rdvService);
                }
                $entityManager->flush(); // Applique les suppressions avant d'ajouter les nouvelles

                // Ajoute les nouvelles relations si des services ont été cochés (submittedServices peut être [] si tout est décoché)
                if (!empty($submittedServices)) {
                    foreach ($submittedServices as $serviceId) {
                        $service = $servicesRepository->find($serviceId);
                        if ($service) {
                            $rdvService = new RendezVousService();
                            $rdvService->setRendezVous($rendezvous);
                            $rdvService->setServices($service);
                            $rdvService->setNiveauService($niveauServiceToUse); // Utilise le niveau déterminé
                            $entityManager->persist($rdvService);
                        } else {
                            // Gérer le cas où un service ID soumis n'est pas trouvé
                            $this->addFlash('danger', 'Un des services sélectionnés n\'est pas valide.');
                            return $this->redirectToRoute('mon_rendez_vous');
                        }
                    }
                }
                // Si submittedServices est [] (vide), cela signifie que tous les services ont été décochés par l'utilisateur.
                // Dans ce cas, après le flush des suppressions, le rendez-vous n'aura plus de services associés.
            } else {
                 // Si $submittedServices est null, l'utilisateur n'a pas touché aux checkboxes des services.
                 // Donc, les services existants et leurs niveaux associés restent inchangés.
            }


            // --- 4. MISE À JOUR DU NIVEAU DE SERVICE (pour les relations existantes si modifiées explicitement) ---
            // Cette partie s'applique si le niveau est changé SANS que les services ne soient changés.
            // Si les services ont été changés ($submittedServices !== null), la logique ci-dessus a déjà géré le niveau pour les NOUVELLES relations.
            // Cette condition est pour le cas où SEUL le niveau de service est mis à jour.
            // CHANGEMENT ICI : 'niveauService' (singulier)
            if ($submittedNiveauServiceId && $submittedNiveauServiceId !== '') { // Si un nouveau niveau a été explicitement sélectionné
                $newNiveau = $niveauServiceRepository->find($submittedNiveauServiceId);
                if ($newNiveau) {
                    // Applique le nouveau niveau à TOUTES les relations RendezVousService associées à ce rendez-vous.
                    // Ceci est important si le rendez-vous avait déjà des services et que SEUL le niveau est changé.
                    foreach ($rendezvous->getRendezVousServices() as $rdvService) {
                        $rdvService->setNiveauService($newNiveau);
                    }
                } else {
                    $this->addFlash('danger', 'Le niveau de service sélectionné n\'est pas valide.');
                    return $this->redirectToRoute('mon_rendez_vous');
                }
            }


            // --- 5. MISE À JOUR JUSTIFICATION ET COMMENTAIRE (facultatifs) ---
            // Ces lignes ne fonctionneront que si vous avez ajouté les propriétés 'justification' et 'commentaire'
            // à votre entité 'RendezVous' et les avez mappées dans votre base de données.
            // Laissez-les commentées si ces propriétés n'existent pas encore dans votre entité RendezVous.
            // $rendezvous->setJustification($request->request->get('justification'));
            // $rendezvous->setCommentaire($request->request->get('commentaire'));


            $entityManager->flush(); // Enregistre toutes les modifications persistées en base de données

            $this->addFlash('success', 'Votre rendez-vous a été mis à jour avec succès !');

        } catch (\Exception $e) {
            // En cas d'erreur inattendue (ex: problème de base de données, validation non gérée)
            $this->addFlash('danger', 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage());
        }

        return $this->redirectToRoute('mon_rendez_vous'); // Redirige toujours vers la page du rendez-vous
    }

    #[Route('/annulerRDV/{id}', name: 'annulerRDV')]
    public function annulerRendezVous(int $id, EntityManagerInterface $entityManager): Response
    {
        $rendezvous = $entityManager->getRepository(RendezVous::class)->find($id);
    
        if (!$rendezvous || $rendezvous->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('danger', 'Rendez-vous non trouvé ou non autorisé.');
            return $this->redirectToRoute('mon_rendez_vous');
        }
    
        // Supprime le rendez-vous (et les RendezVousService associés si vous avez configuré des cascades)
        $entityManager->remove($rendezvous); 
        $entityManager->flush();
    
        $this->addFlash('success', 'Votre rendez-vous a été annulé avec succès.');
    
        return $this->redirectToRoute('mon_rendez_vous');
    }

    #[Route('/enregistrer_avis', name: 'enregistrer_avis', methods: ['POST'])]
    public function enregistrerAvis(
        Request $request,
        EntityManagerInterface $entityManager,
        ServicesRepository $servicesRepository,
        NiveauServiceRepository $niveauServiceRepository
    ): Response {
        // L'utilisateur connecté qui laisse l'avis
        $utilisateur = $this->getUser(); 
    
        $rendezvous = null; 
       
        // 1. Récupération des données du formulaire
        $note = $request->request->get('note'); 
        $titre = $request->request->get('titre'); // Peut être null si non rempli
        
        // CHANGEMENT ICI : 'service' (singulier)
        $serviceId = $request->request->get('service'); 
        
        // C'EST ICI LE PARAMÈTRE POUR L'AVIS : 'niveau_service' (déjà correct)
        $niveauServiceId = $request->request->get('niveau_service'); 

        $commentaire = $request->request->get('commentaire'); 

        // 2. Validation des champs obligatoires (note et commentaire)
        if (!$note || !$commentaire) {
            $this->addFlash('danger', 'Veuillez choisir une note et laisser un commentaire.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        // 3. Validation de la note (entre 1 et 5)
        if (!is_numeric($note) || $note < 1 || $note > 5) {
            $this->addFlash('danger', 'La note doit être un nombre entre 1 et 5.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        // 4. Récupération de l'entité Service si un ID est fourni (optionnel)
        $serviceEntite = null; 
        if ($serviceId) { 
            $serviceEntite = $servicesRepository->find($serviceId);
            if (!$serviceEntite) { // Si un ID est fourni mais ne correspond à aucun service existant
                $this->addFlash('danger', 'Le service sélectionné est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
        }

        // 5. Récupération de l'entité NiveauService si un ID est fourni (optionnel)
        $niveauServiceEntite = null; 
        if ($niveauServiceId) { 
            $niveauServiceEntite = $niveauServiceRepository->find($niveauServiceId);
            if (!$niveauServiceEntite) { // Si un ID est fourni mais ne correspond à aucun niveau de service existant
                $this->addFlash('danger', 'Le niveau de service sélectionné est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
        }

        // 6. Création et hydratation de la nouvelle instance de AvisClient
        $avis = new AvisClient();
        $avis->setNote((int)$note); // Converti la note en entier
        $avis->setTitre($titre); // Assigne le titre (peut être null)
        $avis->setCommentaire($commentaire);
        $avis->setDate(new \DateTimeImmutable()); // Date actuelle de l'avis
        $avis->setUtilisateur($utilisateur); // L'avis est toujours lié à l'utilisateur connecté
        $avis->setService($serviceEntite); // Assigne le service (peut être null)
        $avis->setNiveauService($niveauServiceEntite); // Assigne le niveau de service (peut être null)
        $avis->setRendezVous(null); // Assigne explicitement null pour ne pas lier à un rendez-vous spécifique

        // 7. Persistance de l'avis en base de données
        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Votre avis a été enregistré avec succès et sera visible après validation.');

        return $this->redirectToRoute('mon_rendez_vous');
    }
}