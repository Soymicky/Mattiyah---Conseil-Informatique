<?php
namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        
        $form = $this->createForm(ContactFormType::class); // J'ai supposé que votre formulaire s'appelle ContactFormType
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Prépare l'email
            $email = (new Email())
                ->from($data['email'])
                ->to('mickaellasamx@gmail.com')
                ->subject('[Premier contact] ' . $data['service'])
                ->html($this->renderView('/front/email_contact.html.twig', [
                    'data' => $data,
                ]));
                

            $mailer->send($email);

            $this->addFlash('success', 'Votre message a bien été envoyé !');

            return $this->redirectToRoute('contact');
        }

        return $this->render('/front/contact.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
}