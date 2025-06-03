<?php
namespace App\Controller;

use App\Form\ConnexionFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class ConnexionController extends AbstractController
{
    #[Route('/connexion', name: 'connexion')]
    public function index(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère les erreurs de connexion et le dernier nom d'utilisateur
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Crée le formulaire
        $form = $this->createForm(ConnexionFormType::class, [
            'email' => $lastUsername,
        ]);

        // Traite la requête si le formulaire est soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer la connexion de l'utilisateur ici, si nécessaire

            // Rediriger après la connexion réussie (par exemple, vers la page d'accueil)
            return $this->redirectToRoute('accueil');
        }

        // Retourne la vue du formulaire avec l'erreur de connexion, si elle existe
        return $this->render('/back/connexion.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }
}
