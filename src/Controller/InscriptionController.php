<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\InscriptionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(InscriptionFormType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $form->get('plainPassword')->getData()
            );
            $utilisateur->setMotDePasse($hashedPassword);

            // Définir des valeurs par défaut
            $utilisateur->setStatut('actif');
            $utilisateur->setDtModification(new \DateTime());

            // Enregistrer dans la base de données
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            // Afficher une page de succès temporaire
            return $this->render('back/succes_inscription.html.twig', ['message' => 'Inscription réussie !']);
        }

        return $this->render('back/inscription.html.twig', [
            'inscriptionForm' => $form->createView(),
        ]);
        
    }
}