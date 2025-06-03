<?php
namespace App\Controller;

use App\Entity\Services;
use App\Entity\RendezVous;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class PrendreRDVController extends AbstractController
{
    #[Route('prendre_rdv', name: 'prendre_rdv')]
    public function index(EntityManagerInterface $em): Response
    {
        $utilisateur = $this->getUser();
        $dejaRDV = false;

        if ($utilisateur) {
            $rendezvousExistant = $em->getRepository(RendezVous::class)
                ->findOneBy(['utilisateur' => $utilisateur]);

            if ($rendezvousExistant) {
                $dejaRDV = true;
            }
        }

        $services = $em->getRepository(Services::class)->findAll();
        $typesOffre = $em->getRepository(Services::class)->findUniqueTypesService();

        return $this->render('/back/prendre_rdv.html.twig', [
            'controller_name' => 'PrendreRDVController',
            'services' => $services,
            'typesOffre' => $typesOffre,
            'dejaRDV' => $dejaRDV, // Passer la variable (plus courte) au template
        ]);
    }

    #[Route('/enregistrer_rdv', name: 'enregistrer_rdv', methods: ['POST'])]
    public function enregistrerRdv(Request $request, EntityManagerInterface $em, LoggerInterface $logger): Response
    {
        
        $logger->info('Tentative d\'enregistrement de rendez-vous...');
        $allValues = $request->request->all();
        // $date = $allValues['date'];
        $date = NULL;
        $heure = $allValues['heure'];
        $postServices = $allValues['services'];
        $typeServiceNom = $allValues['typeService'];
        // $heure = $request->request->get('heure');
        // $services = $request->request->get('services');
        //$typeServiceNom = $request->request->get('typeService'); // Récupération de la valeur du champ 'typeService' du formulaire

        // --- AJOUTEZ CES LIGNES POUR LE DÉBOGAGE SI NÉCESSAIRE ---
        // var_dump( $heure, $services, $typeServiceNom);
        // dd($date, $heure, $serviceNom, $typeServiceNom); // Vous pouvez utiliser dd() à la place de dump() pour arrêter l'exécution après l'affichage

        // if (!$date || !$heure || empty($serviceNom) || empty($typeServiceNom)) {
        //     $this->addFlash('danger', 'Tous les champs sont requis.');
        //     return $this->redirectToRoute('prendre_rdv');
        // }

        foreach($postServices as $serv ) {
            $services[] = $em->getRepository(Services::class)->find($serv);
        }
        // if (!$service) {
        //     $this->addFlash('danger', 'Le service sélectionné est invalide.');
        //     return $this->redirectToRoute('prendre_rdv');
        // }
        var_dump($services);
        die;
        try {
            $datetime = new \DateTime("$date $heure");
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Le format de la date ou de l\'heure est invalide.');
            return $this->redirectToRoute('prendre_rdv');
        }

        $rdv = new RendezVous();
        $rdv->setDateRDV($datetime);
        foreach($services as $serv ) {
            $rdv->addService($serv);
        }
        $rdv->setTypeService($typeServiceNom); // Utilisation correcte de la méthode setTypeService() et de la variable $typeServiceNom
        $rdv->setUtilisateur($this->getUser());

        $em->persist($rdv);
        $em->flush();

        $this->addFlash('success', 'Rendez-vous enregistré avec succès !');
        return $this->redirectToRoute('prendre_rdv');
    }
}