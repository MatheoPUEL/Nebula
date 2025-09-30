<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestSimpleResetPassword extends WebTestCase
{
    public function testSimpleResetPassword(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        // Clean database
        $em = $container->get('doctrine')->getManager();
        $userRepository = $container->get(UserRepository::class);

        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }
        $em->flush();
        
        // Reset mailer
        if ($container->has('mailer.message_logger_listener')) {
            $container->get('mailer.message_logger_listener')->reset();
        }

        // Create test user
        $user = (new User())
            ->setUsername('testuser')
            ->setEmail('test@example.com')
            ->setPassword('password')
        ;
        $em->persist($user);
        $em->flush();

        // Test just the form submission
        $crawler = $client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form();
        
        echo "Before submission: " . count($this->getMailerMessages()) . " messages\n";
        
        $form['reset_password_request_form[email]'] = 'test@example.com';
        $client->submit($form);
        
        echo "After submission: " . count($this->getMailerMessages()) . " messages\n";
        
        $messages = $this->getMailerMessages();
        
        self::assertCount(1, $messages, "Expected exactly 1 email, got " . count($messages));
    }
}
