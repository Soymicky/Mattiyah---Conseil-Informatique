<?php

namespace App\Controller;

use App\Repository\RendezVousRepository; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    // On définit une route pour le tableau de bord admin
    #[Route('/admin', name: 'app_admin')]
    public function index(RendezVousRepository $rendezVousRepository): Response // <--
    {
        // recupération des rendez vous avec le repository et findall
        $rendezvous = $rendezVousRepository->findAll();

        return $this->render('admin/index.html.twig', [
            'rendezvous' => $rendezvous, 
        ]);
    }

    // Vous pouvez ajouter d'autres méthodes ici pour la gestion (modifier, supprimer, etc.) plus tard
}