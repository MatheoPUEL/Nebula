<?php

namespace App\Controller\User;

use App\Entity\Country;
use App\Entity\User;
use App\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserSettingsController extends AbstractController
{

    #[Route('/user/{username}/settings/deleteAvatar', name: 'app_user_settings_delete_avatar')]
    public function deleteAvatar(EntityManagerInterface $entityManager): Response
    {
        /* Sécurity selection */
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $UserSession = $this->getUser();
        $UserUpdatingData = $entityManager->getRepository(User::class)->find($UserSession->getId());
        $UserSession = $this->getUser();

        $filesystem = new Filesystem();

        if ($UserSession->getAvatar() != "avatar.png") {
            try {
                $filepath = $this->getParameter('kernel.project_dir') . '/public/user/img/avatar/' . $UserSession->getAvatar();
                $filesystem->remove($filepath);
            } catch (IOExceptionInterface $exception) {
                $this->addFlash('error', 'The delete of the profil picture occured an error.');
                return $this->redirectToRoute('app_user_settings', ['username' => $UserSession->getUsername()]);
            }

            $UserUpdatingData->setAvatar('avatar.png');
            $entityManager->flush();
        }

        if ($UserUpdatingData->getId() == $UserSession->getId()) {
            $this->addFlash('success', 'Your profile picture is successfully deleted. ');
            return $this->redirectToRoute('app_user_settings', ['username' => $UserSession->getUsername()]);
        } else {
            return $this->redirectToRoute('app_home');
        }
    }

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
        // Delete the old avatar and set the new just after
        if ($FormSettings->isSubmitted() && $FormSettings->isValid()) {
            $filesystem = new Filesystem();
            $newAvatar = $FormSettings->get('avatar')->getData();

            // this condition is needed because the 'avatar' field is not required
            // so the image file must be processed only when a file is uploaded
            if ($newAvatar) {
                if ($UserSession->getAvatar() != 'avatar.png') {
                    $filepath = $this->getParameter('kernel.project_dir') . '/public/user/img/avatar/' . $UserSession->getAvatar();
                    $filesystem->remove($filepath);
                }
                // this is needed to safely include the file name as part of the URL
                $newFilename = uniqid() . '.' . $newAvatar->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $newAvatar->move($AvatarDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash("success", "Error");
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $UserUpdatingData->setAvatar($newFilename);

            }

            $UserUpdatingData->setDisplayname($FormSettings->get('display_name')->getData());
            $UserUpdatingData->setEmail($FormSettings->get('email')->getData());
            $UserUpdatingData->setCountry($FormSettings->get('country')->getData());
            $UserUpdatingData->setIsPublic($FormSettings->get('isPublic')->getData());
            $entityManager->flush();

            $this->addFlash('success', 'Your information is succesfully modified');
            return $this->redirectToRoute('app_user_settings', ['username' => $UserSession->getUsername()]);
        }

        $EachCountry = $entityManager->getRepository(Country::class)->findAll();

        if ($UserUpdatingData->getId() == $UserSession->getId()) {
            return $this->render('user/settings.html.twig', [
                'EachCountry' => $EachCountry,
                'formSettings' => $FormSettings,
                'user' => $UserDatabaseUrl
            ]);
        } else {
            return $this->redirectToRoute('app_home');
        }

    }
}
