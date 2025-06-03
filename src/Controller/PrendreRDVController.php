<?php

namespace App\Controller;

use App\Entity\NiveauService;
use App\Entity\RendezVous;
use App\Entity\RendezVousService;
use App\Entity\Services;
use App\Repository\NiveauServiceRepository;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PrendreRDVController extends AbstractController
{
    #[Route('/prendre_rdv', name: 'prendre_rdv')]
    public function index(
        EntityManagerInterface $em,
        ServicesRepository $servicesRepository,
        NiveauServiceRepository $niveauServiceRepository
    ): Response {
        $utilisateur = $this->getUser();
        $dejaRDV = false; // Par défaut, l'utilisateur n'a pas de RDV

        // Si un utilisateur est connecté, on vérifie s'il a déjà un rendez-vous
        if ($utilisateur) {
            // Tente de trouver n'importe quel rendez-vous lié à cet utilisateur
            // (nous ne filtrons plus par statut ici pour simplifier)
            $rendezvousExistant = $em->getRepository(RendezVous::class)
                ->findOneBy(['utilisateur' => $utilisateur]);

            if ($rendezvousExistant) {
                $dejaRDV = true; // Si un RDV est trouvé, on marque que l'utilisateur en a déjà un
            }
        }

        // Récupération de tous les services et niveaux de service pour le formulaire
        $services = $servicesRepository->findAll();
        $niveauService = $niveauServiceRepository->findAll();

        return $this->render('back/prendre_rdv.html.twig', [
            'controller_name' => 'PrendreRDVController',
            'services' => $services,
            'niveauService' => $niveauService,
            'dejaRDV' => $dejaRDV, // Passe la variable au template Twig
        ]);
    }

    #[Route('/enregistrer_rdv', name: 'enregistrer_rdv', methods: ['POST'])]
    public function enregistrerRdv(
        Request $request,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        ServicesRepository $servicesRepository,
        NiveauServiceRepository $niveauServiceRepository
    ): Response {
        $logger->info('Tentative d\'enregistrement de rendez-vous...');

        // Récupération des données du formulaire
        $date = $request->request->get('date');
        $heure = $request->request->get('heure');
        $servicesPost = $request->request->all('services');
        $niveauServicePostId = $request->request->get('niveauService');

        // Validation de base des champs requis
        if (!$date || !$heure || empty($servicesPost) || !$niveauServicePostId) {
            $this->addFlash('danger', 'Tous les champs (Date, Heure, Service(s), Niveau de Service) sont requis.');
            return $this->redirectToRoute('prendre_rdv');
        }

        // Conversion de la date et l'heure en objet DateTime
        try {
            $datetime = new \DateTime("$date $heure");
            $datetime->setTime((int)$datetime->format('H'), (int)$datetime->format('i'), 0);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le format de la date ou de l\'heure est invalide.');
            $logger->error('Erreur de format de date/heure: ' . $e->getMessage());
            return $this->redirectToRoute('prendre_rdv');
        }

        // Vérification de l'utilisateur connecté
        $utilisateur = $this->getUser();
        if (!$utilisateur) {
            $this->addFlash('danger', 'Vous devez être connecté pour prendre rendez-vous.');
            return $this->redirectToRoute('connexion');
        }

        // Récupérer l'objet NiveauService complet
        $niveauServiceEntite = $niveauServiceRepository->find($niveauServicePostId);
        if (!$niveauServiceEntite) {
            $this->addFlash('danger', 'Le niveau de service sélectionné est invalide.');
            return $this->redirectToRoute('prendre_rdv');
        }

        // Création de l'objet RendezVous principal
        $rdv = new RendezVous();
        $rdv->setDateRDV($datetime);
        $rdv->setUtilisateur($utilisateur);
        // Important : Pas de setStatut() ici si vous voulez que le statut soit NULL par défaut
        // et que la colonne soit nullable en DB.
        // Si vous voulez un statut par défaut non NULL, définissez-le ici ou via le constructeur de l'entité.

        // Persiste le RendezVous principal
        $em->persist($rdv);

        // Création et gestion des entités RendezVousService pour chaque service sélectionné
        foreach ($servicesPost as $serviceId) {
            $serviceEntite = $servicesRepository->find($serviceId);
            if ($serviceEntite) {
                $rdvService = new RendezVousService();
                $rdvService->setRendezVous($rdv);
                $rdvService->setServices($serviceEntite);
                $rdvService->setNiveauService($niveauServiceEntite);

                $rdv->addRendezVousService($rdvService); // Ajoute à la collection du RDV principal
                $em->persist($rdvService); // Persiste l'entité d'association
            } else {
                $this->addFlash('danger', "Le service avec l'ID $serviceId est invalide.");
                return $this->redirectToRoute('prendre_rdv');
            }
        }

        // Tentative d'enregistrement en base de données
        try {
            $em->flush(); // Exécute toutes les opérations de persistance

            $this->addFlash('success', 'Rendez-vous enregistré avec succès !');
        } catch (\Exception | \Throwable $e) {
            $this->addFlash('danger', 'Une erreur est survenue lors de l\'enregistrement du rendez-vous. Veuillez réessayer.');
            $logger->error('Erreur lors de l\'enregistrement du RDV: ' . $e->getMessage(), ['exception' => $e]);
        }

        return $this->redirectToRoute('prendre_rdv');
    }
}