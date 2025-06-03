<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FAQController extends AbstractController
{
    #[Route('faq', name: 'faq')]
    public function index(): Response
    {
        return $this->render('/front/faq.html.twig', [
            'controller_name' => 'FAQController',
        ]);
    }
}
