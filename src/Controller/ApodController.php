<?php

namespace App\Controller;

use App\Entity\APOD;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApodController extends AbstractController
{
    #[Route('/apod', name: 'app_apod')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {

        if ($request->isMethod('POST')) {
            // Récupérer la valeur du champ 'nom'
            $apod_date_search = $request->request->get('apod_date_input');

            if (empty($apod_date_search)) {
                $this->addFlash('error', 'Le champ nom est requis.');
            } else {
                $this->addFlash('success', "Formulaire soumis avec succès : $apod_date_search");

                return $this->redirectToRoute('app_apod_search', ['date' => $apod_date_search]);
            }
        }

        $apod = $entityManager->getRepository(APOD::class)->findOneBy([], ['id' => 'DESC']);
        return $this->render('apod/index.html.twig', [
            'last_apod' => $apod
        ]);
    }
    #[Route('/apod/date/{date}', name: 'app_apod_search')]
    public function search(EntityManagerInterface $entityManager, string $date): Response
    {
        $apod = $entityManager->getRepository(APOD::class)->findOneBy(['date' => $date]);
        return $this->render('apod/index.html.twig', [
            'last_apod' => $apod
        ]);
    }
}
