<?php
namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Services; // N'oubliez pas d'utiliser l'entité Services
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MonRendezVousController extends AbstractController
{
    #[Route('mon_rendez_vous', name: 'mon_rendez_vous')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();
        $rendezvous = $entityManager->getRepository(RendezVous::class)
            ->findOneBy(['utilisateur' => $utilisateur]);

        $services = $entityManager->getRepository(Services::class)->findAll();
        $typesOffre = $entityManager->getRepository(Services::class)->findUniqueTypesService();

        return $this->render('/back/mon_rendez_vous.html.twig', [
            'rendezvous' => $rendezvous,
            'services' => $services,
            'typesOffre' => $typesOffre,
        ]);
    }

    // ALORS ICI JE VAIS MODIFIER LE RENDEZ VOUS ET RENVOYER ( RETOURNER ) LA PAGE POUR QU'ELLE S'ACTUALISE 
    // SUR MON RENDEZ VOUS QUI C'EST ENVOYÉ

    #[Route('/modifier_mon_rendezvous/{id}', name: 'modifier_mon_rendezvous', methods: ['POST'])]
    public function modifierRendezVous(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezvous = $entityManager->getRepository(RendezVous::class)->find($id);

        if (!$rendezvous || $rendezvous->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('danger', 'Rendez-vous non trouvé ou non autorisé.');
            return $this->redirectToRoute('mon_rendez_vous');
        }

        $nouvelleDate = $request->request->get('nouvelle_date');
        $nouvelleHeure = $request->request->get('nouvelle_heure');
        $nouveauServiceNom = $request->request->get('service');
        $nouveauTypeServiceNom = $request->request->get('typeService');

        $hasChanged = false;

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

        if ($nouveauServiceNom && $nouveauTypeServiceNom) {
            $nouveauService = $entityManager->getRepository(Services::class)->findOneBy([
                'nomService' => $nouveauServiceNom,
                'typeService' => $nouveauTypeServiceNom,
            ]);

            if ($nouveauService && $nouveauService !== $rendezvous->getService()) {
                $rendezvous->setService($nouveauService);
                $rdvChange = true;
            } elseif (!$nouveauService) {
                $this->addFlash('danger', 'Le service sélectionné pour la modification est invalide.');
                return $this->redirectToRoute('mon_rendez_vous');
            }
        }

        if ($rdvChange) {
            $rendezvous->setStatut('Mis à jour par l\'utilisateur'); 
            $entityManager->flush();
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
    
        $entityManager->remove($rendezvous); // Indique à Doctrine de supprimer l'entité
        $entityManager->flush(); // Exécute les requêtes SQL (ici, la suppression)
    
        $this->addFlash('success', 'Votre rendez-vous a été annulé avec succès.');
    
        return $this->redirectToRoute('mon_rendez_vous');
    }
}