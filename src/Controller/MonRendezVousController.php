<?php
// src/Controller/MonRendezVousController.php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Services;
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

final class MonRendezVousController extends AbstractController
{
    #[Route('mon_rendez_vous', name: 'mon_rendez_vous')]
    public function index(
        EntityManagerInterface $entityManager,
        NiveauServiceRepository $niveauServiceRepository
    ): Response {
        $utilisateur = $this->getUser();
        $rendezvous = null; // Initialisez à null par défaut

        if ($utilisateur) {
            // Cherche le rendez-vous de l'utilisateur connecté.
            // Ceci est utilisé pour l'affichage de la page de rendez-vous.
            $rendezvous = $entityManager->getRepository(RendezVous::class)
                ->findOneBy(['utilisateur' => $utilisateur]);
        }

        // Récupère tous les services et niveaux de service pour les afficher dans les formulaires (rendez-vous et avis)
        $services = $entityManager->getRepository(Services::class)->findAll();
        
        // --- MODIFICATION ICI : On passe la variable 'niveauService' (au singulier) au template ---
        $niveauService = $niveauServiceRepository->findAll();

        return $this->render('/back/mon_rendez_vous.html.twig', [
            'rendezvous' => $rendezvous, // Passé pour l'affichage du rendez-vous
            'services' => $services,     // Passé pour la liste des services
            'niveauService' => $niveauService, // <--- MAINTENANT C'EST 'niveauService' (singulier)
        ]);
    }

    #[Route('/modifier_mon_rdv/{id}', name: 'modifier_mon_rdv', methods: ['POST'])]
    public function modifierRendezVous(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezvous = $entityManager->getRepository(RendezVous::class)->find($id);

        // Vérification de l'existence du rendez-vous et de l'autorisation de l'utilisateur
        if (!$rendezvous || $rendezvous->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('danger', 'Rendez-vous non trouvé ou non autorisé.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        // Récupération des données du formulaire de modification
        $nouvelleDate = $request->request->get('nouvelle_date');
        $nouvelleHeure = $request->request->get('nouvelle_heure');
        $nouveauServiceId = $request->request->get('service');
        
        // --- CORRECTION ICI : Récupération du paramètre 'niveauServices' du formulaire de MODIFICATION ---
        // Ce nom correspond au 'name="niveauServices"' dans votre <select> du formulaire de modification.
        $nouveauNiveauServiceId = $request->request->get('niveauServices'); 

        $hasChanged = false; // Indicateur pour savoir si des modifications ont été apportées

        // 1. Mise à jour de la date et de l'heure du rendez-vous principal
        if ($nouvelleDate && $nouvelleHeure) {
            try {
                $datetime = new \DateTime("$nouvelleDate $nouvelleHeure");
                if ($datetime != $rendezvous->getDateRDV()) {
                    $rendezvous->setDateRDV($datetime);
                    $hasChanged = true;
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Le format de la date ou de l\'heure est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
        }

        // 2. Mise à jour du service et du niveau de service via l'entité RendezVousService
        if ($nouveauServiceId && $nouveauNiveauServiceId) {
            $nouveauServiceEntite = $entityManager->getRepository(Services::class)->find($nouveauServiceId);
            $nouveauNiveauServiceEntite = $entityManager->getRepository(NiveauService::class)->find($nouveauNiveauServiceId);

            if (!$nouveauServiceEntite || !$nouveauNiveauServiceEntite) {
                $this->addFlash('danger', 'Le service ou le niveau de service sélectionné est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }

            // Cherche et modifie le premier RendezVousService lié à ce rendez-vous.
            // Si un rendez-vous peut avoir plusieurs services, la logique ici pourrait être plus complexe.
            $rendezvousServices = $rendezvous->getRendezVousServices();
            if ($rendezvousServices->count() > 0) {
                $rdvServiceToUpdate = $rendezvousServices->first(); // Prend le premier élément de la collection
                
                // Vérifie si le service ou le niveau de service a changé pour marquer 'hasChanged'
                if ($rdvServiceToUpdate->getServices() !== $nouveauServiceEntite ||
                    $rdvServiceToUpdate->getNiveauService() !== $nouveauNiveauServiceEntite) {
                    
                    $rdvServiceToUpdate->setServices($nouveauServiceEntite);
                    $rdvServiceToUpdate->setNiveauService($nouveauNiveauServiceEntite);
                    $hasChanged = true;
                }
            } else {
                // Si le rendez-vous n'a pas encore de RendezVousService (ce qui est rare après la prise de RDV),
                // on en crée un nouveau.
                $newRdvService = new RendezVousService();
                $newRdvService->setRendezVous($rendezvous);
                $newRdvService->setServices($nouveauServiceEntite);
                $newRdvService->setNiveauService($nouveauNiveauServiceEntite);
                $entityManager->persist($newRdvService);
                $rendezvous->addRendezVousService($newRdvService); // Ajoutez à la collection du RDV
                $hasChanged = true;
            }
        }

        // Gestion du statut et des messages flash
        if ($hasChanged) {
            $rendezvous->setStatut('Mis à jour par l\'utilisateur'); // Vous pouvez conserver ou ajuster ce statut
            $entityManager->flush(); // Enregistre toutes les modifications en base de données
            $this->addFlash('success', 'Votre rendez-vous a été mis à jour avec succès.');
        } else {
            $this->addFlash('info', 'Aucune modification n\'a été apportée à votre rendez-vous.');
        }

        return $this->redirectToRoute('mon_rendez_vous');
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
        
        // POUR UN AVIS TYPE GOOGLE : Aucun RendezVous n'est recherché ou lié automatiquement à l'avis.
        // La propriété 'rendezVous' de l'objet AvisClient restera null, car elle est nullable: true dans l'entité.
        $rendezvous = null; // L'avis n'est pas directement lié à un rendez-vous spécifique ici
       
        // 1. Récupération des données du formulaire
        $note = $request->request->get('note'); 
        $titre = $request->request->get('titre'); // Peut être null si non rempli
        $serviceId = $request->request->get('service'); // Peut être null si non sélectionné
        
        // --- C'EST ICI LE PARAMÈTRE POUR L'AVIS : 'niveau_service' ---
        // Ce nom correspond au 'name="niveau_service"' dans votre <select> du formulaire d'avis.
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