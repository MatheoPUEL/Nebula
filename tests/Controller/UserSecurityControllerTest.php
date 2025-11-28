<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserSecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $user;
    private const TEST_USERNAME = 'testuser';
    private const TEST_PASSWORD = 'oldpassword123';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $this->user = (new User())
            ->setEmail('test@example.com')
            ->setUsername(self::TEST_USERNAME)
            ->setDisplayname('Test User')
            ->setAvatar('default.png')
            ->setDescription('Test user description');
        $this->user->setPassword($passwordHasher->hashPassword($this->user, self::TEST_PASSWORD));

        $em->persist($this->user);
        $em->flush();

        // Log in the user
        $this->client->loginUser($this->user);
    }

    public function testSecurityPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/user/' . self::TEST_USERNAME . '/security');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Password change');
        self::assertSelectorExists('input[name="user_settings_change_pwd[currentPassword]"]');
        self::assertSelectorExists('input[name="user_settings_change_pwd[newPassword]"]');
        self::assertSelectorExists('input[name="user_settings_change_pwd[confirmPassword]"]');
    }

    public function testPasswordChangedSuccessfullyWithValidInputs(): void
    {
        $crawler = $this->client->request('GET', '/user/' . self::TEST_USERNAME . '/security');

        $form = $crawler->selectButton('Save changes')->form([
            'user_settings_change_pwd[currentPassword]' => self::TEST_PASSWORD,
            'user_settings_change_pwd[newPassword]' => 'newpassword123',
            'user_settings_change_pwd[confirmPassword]' => 'newpassword123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/user/' . self::TEST_USERNAME . '/security');
        $this->client->followRedirect();

        self::assertSelectorExists('.alert-success');
        self::assertSelectorTextContains('.alert-success', 'Password changed successfully.');

        // Verify the password was actually changed in the database
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->refresh($this->user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');
        self::assertTrue($passwordHasher->isPasswordValid($this->user, 'newpassword123'));
    }

    public function testPasswordChangeFailsIfCurrentPasswordIsIncorrect(): void
    {
        $crawler = $this->client->request('GET', '/user/' . self::TEST_USERNAME . '/security');

        $form = $crawler->selectButton('Save changes')->form([
            'user_settings_change_pwd[currentPassword]' => 'wrongpassword',
            'user_settings_change_pwd[newPassword]' => 'newpassword123',
            'user_settings_change_pwd[confirmPassword]' => 'newpassword123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/user/' . self::TEST_USERNAME . '/security');
        $this->client->followRedirect();

        self::assertSelectorExists('.alert-danger');
        self::assertSelectorTextContains('.alert-danger', 'Current password is incorrect.');

        // Verify the password was NOT changed in the database
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->refresh($this->user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');
        self::assertTrue($passwordHasher->isPasswordValid($this->user, self::TEST_PASSWORD));
        self::assertFalse($passwordHasher->isPasswordValid($this->user, 'newpassword123'));
    }

    public function testPasswordChangeFailsIfNewPasswordAndConfirmationDoNotMatch(): void
    {
        $crawler = $this->client->request('GET', '/user/' . self::TEST_USERNAME . '/security');

        $form = $crawler->selectButton('Save changes')->form([
            'user_settings_change_pwd[currentPassword]' => self::TEST_PASSWORD,
            'user_settings_change_pwd[newPassword]' => 'newpassword123',
            'user_settings_change_pwd[confirmPassword]' => 'differentpassword123',
        ]);

        $this->client->submit($form);

        self::assertResponseRedirects('/user/' . self::TEST_USERNAME . '/security');
        $this->client->followRedirect();

        self::assertSelectorExists('.alert-danger');
        self::assertSelectorTextContains('.alert-danger', 'New password and confirmation do not match.');

        // Verify the password was NOT changed in the database
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->refresh($this->user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');
        self::assertTrue($passwordHasher->isPasswordValid($this->user, self::TEST_PASSWORD));
        self::assertFalse($passwordHasher->isPasswordValid($this->user, 'newpassword123'));
    }

    public function testPasswordChangeFailsIfNewPasswordDoesNotMeetLengthRequirements(): void
    {
        $crawler = $this->client->request('GET', '/user/' . self::TEST_USERNAME . '/security');

        $form = $crawler->selectButton('Save changes')->form([
            'user_settings_change_pwd[currentPassword]' => self::TEST_PASSWORD,
            'user_settings_change_pwd[newPassword]' => 'short',
            'user_settings_change_pwd[confirmPassword]' => 'short',
        ]);

        $this->client->submit($form);

        // Form validation should fail, so we stay on the same page (no redirect)
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.invalid-feedback');
        self::assertSelectorTextContains('.invalid-feedback', 'Password must be at least 8 characters long.');

        // Verify the password was NOT changed in the database
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $em->refresh($this->user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');
        self::assertTrue($passwordHasher->isPasswordValid($this->user, self::TEST_PASSWORD));
        self::assertFalse($passwordHasher->isPasswordValid($this->user, 'short'));
    }
}
