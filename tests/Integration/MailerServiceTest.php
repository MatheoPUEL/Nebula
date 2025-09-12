<?php
namespace App\Tests\Integration;

use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Test\Constraint\EmailCount;
use Symfony\Component\Mailer\Test\Constraint\EmailIsQueued;

class MailerServiceTest extends KernelTestCase
{
    private MailerService $mailerService;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->mailerService = $container->get(MailerService::class);
    }

    public function testSendMail(): void
    {
        // Send an email
        $this->mailerService->sendMail(
            'test@example.com',
            'Sujet Test',
            'Ceci est un mail de test'
        );

        // Using Symfony's mailer test assertions
        $this->assertEmailCount(1);
        
        $email = $this->getMailerMessage();
        $this->assertEmailAddressContains($email, 'to', 'test@example.com');
        $this->assertEmailSubjectContains($email, 'Sujet Test');
        $this->assertEmailTextBodyContains($email, 'Ceci est un mail de test');
    }
}
