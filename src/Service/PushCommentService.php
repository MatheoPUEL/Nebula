<?php

namespace App\Service;

use App\Entity\Comment;
use App\Form\PushCommentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class PushCommentService
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private EntityManagerInterface $em
    ) {}

    /**
     * Handle comment creation (base comment or reply)
     */
    public function handle(
        Request $request,
        ?UserInterface $user,
        string $targetType,
        int $targetID,
        ?Comment $parent = null // <- important !
    ) {
        // Nouvelle entité commentaire
        $comment = new Comment();

        // Si c'est une réponse → on lie le parent
        if ($parent !== null) {
            $comment->setParent($parent);
        }

        $form = $this->formFactory->create(PushCommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $comment->setAuthor($user);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setTargetType($targetType);
            $comment->setTargetId($targetID);



            $this->em->persist($comment);
            $this->em->flush();

            return $comment;
        }

        return $form;
    }
}
