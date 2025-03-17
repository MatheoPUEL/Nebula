<?php
namespace App\Controller;

use App\Entity\APOD;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

session_start();

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'HomeController',
        ]);

    }

    #[Route('/tests', name: 'app_tests')]
    public function test(EntityManagerInterface $entityManager): Response
    {
        $dateInitiale = "1995-06-16"; // Format AAAA-MM-JJ "1995-06-16"

        // Obtenir la date actuelle
        $dateActuelle = date("Y-m-d");

        // Convertir les dates en objets DateTime
        if (isset($_SESSION['increment_date'])) {
            $dateDebut = $_SESSION['increment_date'];
        } else {
            $dateDebut = new \DateTime($dateInitiale);
        }
        $dateFin = new \DateTime($dateActuelle);

        // Stocker les résultats dans une variable
        $dates = "";
        $compteur = 0;


        if ($dateDebut == $dateFin) {
            return new Response('Terminé');
        }

            $url = "https://api.nasa.gov/planetary/apod?api_key=ascl6VJGblWGUvzuEUpFPq4J2K7SUbIgknDxIPrV&date=".$dateDebut->format("Y-m-d");
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo "Erreur cURL : " . curl_error($ch);
            }

            curl_close($ch);

            $data = json_decode($response, true);

            $product = new APOD();
            if (isset($data['title'])) {
                $product->setTitle($data['title']);
            } else {
                $product->setTitle('no_title');
            }

            if (isset($data['date'])) {
                $product->setDate($data['date']);
            } else {
                $dateString = date_format($_SESSION['increment_date'], 'Y-m-d');
                $product->setDate($dateString);
            }
            if (isset($data['copyright'])) {
                $product->setCopyright($data['copyright']);
            } else {
                $product->setCopyright('no_copyright');
            }
            if (isset($data['explanation'])) {
                $product->setExplanation($data['explanation']);
            } else {
                $product->setExplanation('no_explanation');
            }
            if (isset($data['hdurl'])) {
                $product->setHdurl($data['hdurl']);
            } else {
                $product->setHdurl('no_hdurl');
            }
            if (isset($data['media_type'])) {
                if ($data['media_type'] == "image") {
                    $product->setMediaType(1);

                } elseif ($data['media_type'] == "video") {
                    $product->setMediaType(2);
                } else {
                    $product->setMediaType(3);

                }
            } else {
                $product->setMediaType(0);
            }
            if (isset($data['url'])) {
                $product->setUrl($data['url']);
            } else {
                $product->setUrl("no_url");
            }

            $entityManager->persist($product);

            $entityManager->flush();

            $dates .= $dateDebut->format("Y-m-d") . $compteur ."<br>";
            $_SESSION['increment_date'] = $dateDebut->modify("+1 day");
            // $dateDebut->modify("+1 day")
            return $this->redirectToRoute('app_home');

    }

    #[Route('/test-mail', name: 'app_test-mail')]
    public function testmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('hello@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);
        return $this->render('index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
