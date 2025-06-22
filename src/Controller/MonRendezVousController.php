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
        NiveauServiceRepository $niveauServiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        
        $utilisateur = $this->getUser();
        $rendezvous = null;

        if ($utilisateur) {
            $rendezvous = $entityManager->getRepository(RendezVous::class)
                ->findOneBy(
                    ['utilisateur' => $utilisateur],
                ['dateRDV' => 'DESC']
            );
        }

        $services = $servicesRepository->findAll();
        $niveauService = $niveauServiceRepository->findAll();
        

        return $this->render('/back/mon_rendez_vous.html.twig', [
            'rendezvous' => $rendezvous,
            'services' => $services,
            'niveauService' => $niveauService,
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

        if (!$rendezvous || $rendezvous->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('danger', 'Rendez-vous non trouvé ou non autorisé.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        try {
            // --- 1. MISE À JOUR DATE ET HEURE ---

            // J'AI CORRIGÉ LES NOMS DES CHAMPS ICI pour correspondre à votre Twig :
            // 'nouvelle_date' pour la date et 'heure' pour l'heure.
            $nouvelleDate = $request->request->get('nouvelle_date'); 
            $nouvelleHeure = $request->request->get('heure');     


            if (!$nouvelleDate || !$nouvelleHeure) {
                 $this->addFlash('danger', 'La date et l\'heure sont obligatoires pour la modification.');
                 return $this->redirectToRoute('mon_rendez_vous');
            }

            // MODIFICATION ICI : 'd F Y' pour correspondre au format de Flatpickr (Jour MoisComplet Année Heure:Minute)
        
            $dateRDV = \DateTime::createFromFormat('Y-m-d H:i', "$nouvelleDate $nouvelleHeure");
            
            
            if (!$dateRDV) {
                $this->addFlash('danger', 'Le format de la date ou de l\'heure n\'est pas valide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
            $rendezvous->setDateRDV($dateRDV);


            // --- 2. DÉTERMINATION DU NIVEAU DE SERVICE À UTILISER (pour les relations RendezVousService) ---
            $submittedNiveauServiceId = $request->request->get('niveauService');
            $niveauServiceToUse = null;

            if ($submittedNiveauServiceId && $submittedNiveauServiceId !== '') {
                $niveauServiceToUse = $niveauServiceRepository->find($submittedNiveauServiceId);
            } else {
                $currentRendezVousService = $rendezvous->getRendezVousServices()->first();
                if ($currentRendezVousService) {
                    $niveauServiceToUse = $currentRendezVousService->getNiveauService();
                }
            }

            if (!$niveauServiceToUse) {
                $this->addFlash('danger', 'Un niveau de service doit être défini pour les services sélectionnés.');
                return $this->redirectToRoute('mon_rendez_vous');
            }


            // --- 3. MISE À JOUR DES SERVICES (si l'utilisateur a interagi avec les checkboxes 'service[]') ---
            $submittedServices = $request->request->all('service');

            if ($submittedServices !== null) {
                foreach ($rendezvous->getRendezVousServices() as $rdvService) {
                    $entityManager->remove($rdvService);
                }
                $entityManager->flush();

                if (!empty($submittedServices)) {
                    foreach ($submittedServices as $serviceId) {
                        $service = $servicesRepository->find($serviceId);
                        if ($service) {
                            $rdvService = new RendezVousService();
                            $rdvService->setRendezVous($rendezvous);
                            $rdvService->setServices($service);
                            $rdvService->setNiveauService($niveauServiceToUse);
                            $entityManager->persist($rdvService);
                        } else {
                            $this->addFlash('danger', 'Un des services sélectionnés n\'est pas valide.');
                            return $this->redirectToRoute('mon_rendez_vous');
                        }
                    }
                }
            }


            // --- 4. MISE À JOUR DU NIVEAU DE SERVICE (pour les relations existantes si modifiées explicitement) ---
            if ($submittedNiveauServiceId && $submittedNiveauServiceId !== '') {
                $newNiveau = $niveauServiceRepository->find($submittedNiveauServiceId);
                if ($newNiveau) {
                    foreach ($rendezvous->getRendezVousServices() as $rdvService) {
                        $rdvService->setNiveauService($newNiveau);
                    }
                } else {
                    $this->addFlash('danger', 'Le niveau de service sélectionné n\'est pas valide.');
                    return $this->redirectToRoute('mon_rendez_vous');
                }
            }


            // --- 5. MISE À JOUR JUSTIFICATION ET COMMENTAIRE (facultatifs) ---
            // N'oubliez pas de décommenter ces lignes si vous avez ajouté ces propriétés à votre entité RendezVous.
            // $rendezvous->setJustification($request->request->get('justification'));
            // $rendezvous->setCommentaire($request->request->get('commentaire'));


            $entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été mis à jour avec succès !');

        } catch (\Exception $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de la mise à jour : ' . $e->getMessage());
        }

        return $this->redirectToRoute('mon_rendez_vous');
    }

    #[Route('/annulerRDV/{id}', name: 'annulerRDV', methods: ['POST'])] // Ajout de methods: ['POST']
    public function annulerRendezVous(
        int $id,
        Request $request, // Injection de l'objet Request
        EntityManagerInterface $entityManager
    ): Response
    {
        $rendezvous = $entityManager->getRepository(RendezVous::class)->find($id);
    
        if (!$rendezvous || $rendezvous->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('danger', 'Rendez-vous non trouvé ou non autorisé.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        // Récupérer la justification et le commentaire du formulaire
        $justification = $request->request->get('justification_cancel');
        $commentaire = $request->request->get('commentaire_annul_rdv');

        // --- C'est ici que vous décidez quoi faire avec la justification et le commentaire ---
        // Option 1: Ajouter ces informations à votre entité RendezVous avant de la supprimer.
        //           Cela nécessiterait que vous ayez des propriétés comme 'justificationAnnulation'
        //           et 'commentaireAnnulation' dans votre entité RendezVous.
        // Exemple (si vous avez les propriétés dans RendezVous):
        // $rendezvous->setJustificationAnnulation($justification);
        // $rendezvous->setCommentaireAnnulation($commentaire);
        // $entityManager->persist($rendezvous); // Peut être nécessaire si vous modifiez l'entité avant de la supprimer

        // Option 2 (recommandée pour un historique propre): Créer une entité d'historique des annulations.
        //          Cela permet de garder une trace complète de toutes les annulations,
        //          même après la suppression du rendez-vous original.
        //
        // Pour cela, vous auriez besoin d'une entité comme 'AnnulationHistorique' (à créer):
        // class AnnulationHistorique {
        //     // ... propriétés (id, rendezVousId, utilisateurId, justification, commentaire, dateAnnulation)
        // }
        //
        // Et vous l'utiliseriez comme ceci (exemple, après avoir créé l'entité et son repository):
        // use App\Entity\AnnulationHistorique; // N'oubliez pas d'ajouter cette ligne au début du fichier
        // $historiqueAnnulation = new AnnulationHistorique();
        // $historiqueAnnulation->setRendezVousId($rendezvous->getId()); // Ou $rendezvous si vous avez une relation ManyToOne
        // $historiqueAnnulation->setUtilisateur($this->getUser());
        // $historiqueAnnulation->setJustification($justification);
        // $historiqueAnnulation->setCommentaire($commentaire);
        // $historiqueAnnulation->setDateAnnulation(new \DateTimeImmutable());
        // $entityManager->persist($historiqueAnnulation);
        // $entityManager->flush(); // Flush ici si vous voulez que l'historique soit enregistré avant la suppression du rdv

        // Ensuite, supprimez le rendez-vous.
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
        $utilisateur = $this->getUser(); 
    
        $rendezvous = null; 
       
        $note = $request->request->get('note'); 
        $titre = $request->request->get('titre');
        $serviceId = $request->request->get('service'); 
        $niveauServiceId = $request->request->get('niveau_service'); 
        $commentaire = $request->request->get('commentaire'); 

        if (!$note || !$commentaire) {
            $this->addFlash('danger', 'Veuillez choisir une note et laisser un commentaire.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        if (!is_numeric($note) || $note < 1 || $note > 5) {
            $this->addFlash('danger', 'La note doit être un nombre entre 1 et 5.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        $serviceEntite = null; 
        if ($serviceId) { 
            $serviceEntite = $servicesRepository->find($serviceId);
            if (!$serviceEntite) {
                $this->addFlash('danger', 'Le service sélectionné est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
        }

        $niveauServiceEntite = null; 
        if ($niveauServiceId) { 
            $niveauServiceEntite = $niveauServiceRepository->find($niveauServiceId);
            if (!$niveauServiceEntite) {
                $this->addFlash('danger', 'Le niveau de service sélectionné est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
        }

        $avis = new AvisClient();
        $avis->setNote((int)$note);
        $avis->setTitre($titre);
        $avis->setCommentaire($commentaire);
        $avis->setDate(new \DateTimeImmutable());
        $avis->setUtilisateur($utilisateur);
        $avis->setService($serviceEntite);
        $avis->setNiveauService($niveauServiceEntite);
        $avis->setRendezVous(null);

        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Votre avis a été enregistré avec succès et sera visible après validation.');

        return $this->redirectToRoute('mon_rendez_vous');
    }
}