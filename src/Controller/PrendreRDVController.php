<?php

namespace App\Controller;

use App\Entity\NiveauService;
use App\Entity\RendezVous;
use App\Entity\Services;
use App\Repository\NiveauServiceRepository;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

final class PrendreRDVController extends AbstractController
{
    #[Route('/prendre_rdv', name: 'prendre_rdv')]
    public function index(
        EntityManagerInterface $em,
        ServicesRepository $servicesRepository,
        NiveauServiceRepository $niveauServiceRepository
    ): Response {
        $utilisateur = $this->getUser();
        $dejaRDV = false;

        if ($utilisateur) {
            $rendezvousExistant = $em->getRepository(RendezVous::class)
                ->findOneBy(['utilisateur' => $utilisateur]);

            if ($rendezvousExistant) {
                $dejaRDV = true;
            }
        }

        $services = $servicesRepository->findAll(); 
        $niveauService = $niveauServiceRepository->findAll();

        return $this->render('back/prendre_rdv.html.twig', [
            'controller_name' => 'PrendreRDVController',
            'services' => $services,
            'niveauService' => $niveauService,
            'dejaRDV' => $dejaRDV,
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

        $date = $request->request->get('date');
        $heure = $request->request->get('heure');
        $Service = $request->request->all('services');
        $niveauService = $request->request->get('niveauService'); 

        if (!$date || !$heure || empty($postServiceIds) || !$niveauServiceId) {
            $this->addFlash('danger', 'Tous les champs sont requis.');
            return $this->redirectToRoute('prendre_rdv');
        }
        
        try {
            $datetime = new \DateTime("$date $heure");
            $datetime->setTime($datetime->format('H'), $datetime->format('i'), 0);
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le format de la date ou de l\'heure est invalide.');
            $logger->error('Erreur de format de date/heure: ' . $e->getMessage());
            return $this->redirectToRoute('prendre_rdv');
        }

        $rdv = new RendezVous();

        foreach ($Service as $serviceId) {
            $service = $servicesRepository->find($serviceId);
            if ($service) {
                $rdv->addService($service);
            } else {
                $this->addFlash('danger', "Le service avec l'ID $serviceId est invalide.");
                return $this->redirectToRoute('prendre_rdv');
            }
        }
        
        $niveauService = $niveauServiceRepository->find($niveauServiceId);
        if (!$niveauService) {
            $this->addFlash('danger', 'Le niveau de service sélectionné est invalide.');
            return $this->redirectToRoute('prendre_rdv');
        }
        // CORRECTION MAJEURE ICI : Utilisation de setTypeService() au lieu de setNiveauService()
        // Ceci suppose que votre entité RendezVous a une propriété $typeService pour la relation NiveauService
        $rdv->setTypeService($niveauService); 

        $rdv->setDateRDV($datetime);
        $rdv->setUtilisateur($this->getUser());
        $rdv->setStatut('en attente');

        try {
            $em->persist($rdv);
            $em->flush();

            $this->addFlash('success', 'Rendez-vous enregistré avec succès !');
        } catch (\Exception | \Throwable $e) { 
            $this->addFlash('danger', 'Une erreur est survenue lors de l\'enregistrement du rendez-vous. Veuillez réessayer.');
            $logger->error('Erreur lors de l\'enregistrement du RDV: ' . $e->getMessage(), ['exception' => $e]);
        }

        return $this->redirectToRoute('prendre_rdv');
    }
}