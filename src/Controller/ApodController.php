<?php

namespace App\Controller;

use App\Entity\Apod;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApodController extends AbstractController
{

    #[Route('/apod', name: 'app_apod')]
    public function apod(EntityManagerInterface $entityManager): Response
    {

        $apod = $entityManager->getRepository(Apod::class)->findOneBy([], ['id' => 'desc']);

        return $this->render('apod.html.twig', ['apod'=>$apod]);
    }

    #[Route('/apod/{date}', name: 'app_apod_date')]
    public function apodDate(string $date, EntityManagerInterface $entityManager): Response
    {
        try {
            $dateObj = new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            return $this->render('apod.html.twig', [
                'error' => $date
            ]);
        }
        $apod = $entityManager
            ->getRepository(Apod::class)
            ->findOneBy(['date_apod' => $dateObj]);

        if (!$apod) {
            return $this->render('apod.html.twig', [
                'apod' => $apod,
                'error' => $date
            ]);
        }

        return $this->render('apod.html.twig', [
            'apod' => $apod
        ]);
    }

}
