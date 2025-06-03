<?php
// src/Controller/MonRendezVousController.php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Services;
use App\Entity\NiveauService; // Ajoutez l'import pour NiveauService
use App\Entity\RendezVousService; // Ajoutez l'import pour RendezVousService
use App\Repository\NiveauServiceRepository; // Ajoutez l'import pour NiveauServiceRepository
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
        NiveauServiceRepository $niveauServiceRepository // Injectez le NiveauServiceRepository ici
    ): Response {
        $utilisateur = $this->getUser();
        $rendezvous = null; // Initialisez à null par défaut

        if ($utilisateur) {
            // Cherche le rendez-vous de l'utilisateur connecté
            $rendezvous = $entityManager->getRepository(RendezVous::class)
                ->findOneBy(['utilisateur' => $utilisateur]);
        }

        // Récupère tous les services pour le formulaire de modification (select 'service')
        $services = $entityManager->getRepository(Services::class)->findAll();
        
        // Récupère tous les niveaux de service pour le formulaire de modification (select 'typeService')
        // C'est ici que l'erreur 'typeService' était indirectement causée.
        $typesOffre = $niveauServiceRepository->findAll(); // Correction: on récupère les NiveauService

        return $this->render('/back/mon_rendez_vous.html.twig', [
            'rendezvous' => $rendezvous,
            'services' => $services, // Passé au Twig pour la liste des services
            'typesOffre' => $typesOffre, // Passé au Twig pour la liste des niveaux de service
        ]);
    }

    #[Route('/modifier_mon_rendezvous/{id}', name: 'modifier_mon_rendezvous', methods: ['POST'])]
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
        // On récupère les IDs des services et niveaux de service, pas leurs noms directement
        $nouveauServiceId = $request->request->get('service');
        $nouveauNiveauServiceId = $request->request->get('typeService'); // 'typeService' fait référence à l'ID du NiveauService ici

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

            // Pour l'exemple, nous allons chercher et modifier le premier RendezVousService lié à ce rendez-vous.
            // Si un rendez-vous peut avoir plusieurs services (ce que votre entité RendezVousService suggère),
            // la logique pourrait être plus complexe pour gérer plusieurs associations.
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
                // Si le rendez-vous n'a pas encore de RendezVousService (ce qui ne devrait pas arriver après la prise de RDV),
                // on peut en créer un nouveau pour le lier.
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
    
        // Si vous avez des relations en cascade (cascade={"remove"}) sur RendezVousService
        // depuis RendezVous, la suppression de RendezVous suffira.
        // Sinon, vous pourriez avoir besoin de supprimer les RendezVousService associés d'abord.
        $entityManager->remove($rendezvous); // Indique à Doctrine de supprimer l'entité RendezVous
        $entityManager->flush(); // Exécute la suppression en base de données
    
        $this->addFlash('success', 'Votre rendez-vous a été annulé avec succès.');
    
        return $this->redirectToRoute('mon_rendez_vous');
    }
}