<?php

namespace App\Controller;

use App\Service\MailerService;
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
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'HomeController',
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
