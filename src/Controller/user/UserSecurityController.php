<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserSettingsChangePWDType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class UserSecurityController extends AbstractController
{
    #[Route('/user/{username}/security', name: 'app_user_security')]
    public function index(
        string $username,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        $form = $this->createForm(UserSettingsChangePWDType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
                return $this->redirectToRoute('app_user_security', ['username' => $username]);
                exit();
            } elseif ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'New password and confirmation do not match.');
                return $this->redirectToRoute('app_user_security', ['username' => $username]);
            } else {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
                $entityManager->flush();
                $this->addFlash('success', 'Password changed successfully.');
                return $this->redirectToRoute('app_user_security', ['username' => $username]);
            }
        }

        return $this->render('user/security.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
