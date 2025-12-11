<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentController extends AbstractController
{
    #[Route('/comment/reply/{parentId}', name: 'app_comment_reply', methods: ['POST'])]
    public function reply(
        int $parentId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        $parent = $em->getRepository(Comment::class)->find($parentId);
        if (!$parent) {
            throw $this->createNotFoundException('Le commentaire parent est introuvable.');
        }

        $content = $request->request->get('content');

        if (!$content || trim($content) === '') {
            throw $this->createNotFoundException('Le contenu du commentaire est vide.');
        }

        $reply = new Comment();
        $reply->setParent($parent);
        $reply->setAuthor($user);
        $reply->setTargetType($parent->getTargetType());
        $reply->setTargetId($parent->getTargetId());
        $reply->setContent($content);
        $reply->setCreatedAt(new \DateTimeImmutable());

        $em->persist($reply);
        $em->flush();

        // Redirection vers l’URL d’origine
        $returnUrl = $request->request->get('return_url') ?? $this->generateUrl('app_apod');

        return $this->redirect($returnUrl);
    }
}
