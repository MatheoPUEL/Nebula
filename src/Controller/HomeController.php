<?php

namespace App\Controller;

use App\Entity\Apod;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private MailerService $mailerService;

    public function __construct(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }

    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $last_apod = $entityManager->getRepository(Apod::class)->findOneBy([], ['id' => 'DESC']);

        return $this->render('index.html.twig', [
            'last_apod' => $last_apod,
        ]);
    }

    #[Route('/sendMail', name: 'app_private_sendMail')]
    public function sendMail(): Response
    {
        $this->mailerService->sendMail(
            'test@example.com',
            'Sujet Test',
            'Ceci est un mail de test'
        );
        return new Response("<p>The mail has been send</p>");
    }
}
