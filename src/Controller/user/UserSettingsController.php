<?php

namespace App\Controller\user;

use App\Entity\User;
use App\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserSettingsController extends AbstractController
{

    #[Route('/user/{username}/settings', name: 'app_user_settings')]
    public function index(string $username, EntityManagerInterface $entityManager, Request $request, #[Autowire('user/img/avatar')] string $AvatarDirectory): Response
    {
        /* Sécurity selection */
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $UserDatabaseUrl = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        $UserSession = $this->getUser();

        /* Start process of the form */
        $UserUpdatingData = $entityManager->getRepository(User::class)->find($UserSession->getId());

        $FormSettings = $this->createForm(UserSettingsType::class, $this->getUser());
        $FormSettings->handleRequest($request);

        if ($FormSettings->isSubmitted() && $FormSettings->isValid()) {

            $newAvatar = $FormSettings->get('avatar')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($newAvatar) {
                $originalFilename = pathinfo($newAvatar->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newFilename = uniqid().'.'.$newAvatar->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $newAvatar->move($AvatarDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash("success", "et non ça na pas marché ".$e);
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $UserUpdatingData->setAvatar($newFilename);
            }


            $UserUpdatingData->setDisplayname($FormSettings->get('display_name')->getData());

            $UserUpdatingData->setEmail($FormSettings->get('email')->getData());

            $entityManager->flush();

            $this->addFlash('success','Your information is succesfully modified');
            return $this->redirectToRoute('app_user_settings', ['username' => $UserSession->getUsername()]);
        }



        if ($UserUpdatingData->getId() == $UserSession->getId()) {
            return $this->render('user/settings.html.twig', [
                'formSettings' => $FormSettings,
                'user' => $UserDatabaseUrl
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }

    }
}
