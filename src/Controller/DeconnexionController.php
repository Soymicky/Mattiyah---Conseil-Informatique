<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface; // Ajoute cette ligne

final class DeconnexionController extends AbstractController
{
    #[Route('/deconnexion', name: 'deconnexion')] // Nom de la route modifié
    public function logout(SessionInterface $session): Response // Nom de la méthode changé et ajout de l'argument SessionInterface
    {
        $session->clear(); // Supprime toute la session
        return $this->redirectToRoute('accueil'); // Redirige vers la route 'accueil'
    }
}