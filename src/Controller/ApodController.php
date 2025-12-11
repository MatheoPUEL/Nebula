<?php

namespace App\Controller;

use App\Entity\Apod;
use App\Entity\Comment;
use App\Service\PushCommentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApodController extends AbstractController
{
    #[Route('/apod', name: 'app_apod')]
    public function apod(EntityManagerInterface $entityManager, Request $request, PushCommentService $handler): Response
    {
        $user = $this->getUser();
        $apod = $entityManager->getRepository(Apod::class)->findOneBy([], ['id' => 'desc']);
        $comments = $entityManager->getRepository(Comment::class)->findBy([
            'targetType' => 'apod',
            'targetId' => $apod->getId()
        ], ['createdAt' => 'ASC']);

        // Commentaires
        $result = $handler->handle($request, $user, 'apod', $apod->getId());

        if ($result instanceof Comment) {
            return $this->redirectToRoute('app_apod');
        }

        return $this->render('apod.html.twig', [
            'form' => $result->createView(),
            'apod' => $apod,
            'comments' => $comments,
        ]);
    }

    #[Route('/apod/{date}', name: 'app_apod_date')]
    public function apodDate(string $date, EntityManagerInterface $entityManager, Request $request, PushCommentService $handler): Response
    {
        try {
            $dateObj = new \DateTimeImmutable($date);
        } catch (\Exception $e) {
            return $this->render('apod.html.twig', [
                'error' => $date
            ]);
        }

        $apod = $entityManager->getRepository(Apod::class)->findOneBy(['date_apod' => $dateObj]);
        $comments = $entityManager->getRepository(Comment::class)->findBy([
            'targetType' => 'apod',
            'targetId' => $apod->getId()
        ], ['createdAt' => 'ASC']);
        $user = $this->getUser();
        $result = $handler->handle($request, $user, 'apod', $apod->getId());

        if ($result instanceof Comment) {
            return $this->redirectToRoute('app_apod_date', ['date' => $date]);
        }

        if (!$apod) {
            return $this->render('apod.html.twig', [
                'apod' => null,
                'error' => $date
            ]);
        }

        return $this->render('apod.html.twig', [
            'form' => $result->createView(),
            'apod' => $apod,
            'comments' => $comments,
        ]);
    }
}
