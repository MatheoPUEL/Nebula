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
    public function index(EntityManagerInterface $entityManager): Response
    {

        $apod = $entityManager->getRepository(Apod::class)->findOneBy([], ['id' => 'desc']);

        return $this->render('apod.html.twig', ['apod'=>$apod]);
    }


}
