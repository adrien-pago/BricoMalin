<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class JobRequestTest extends WebTestCase
{
    public function testCreateJobRequest(): void
    {
        $client = static::createClient();
        
        // Créer un utilisateur et récupérer le token
        $userRepository = static::getContainer()->get(UserRepository::class);
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        $user = new User();
        $user->setEmail('jobtest@example.com');
        $user->setPasswordHash($passwordHasher->hashPassword($user, 'password123'));
        $userRepository->save($user, true);

        // Se connecter pour obtenir le token
        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'jobtest@example.com',
                'password' => 'password123',
            ])
        );

        $loginResponse = json_decode($client->getResponse()->getContent(), true);
        $token = $loginResponse['token'] ?? null;
        $this->assertNotNull($token);

        // Créer une demande
        $client->request(
            'POST',
            '/api/job-requests',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ],
            json_encode([
                'categoryId' => 1,
                'title' => 'Test demande',
                'description' => 'Description de test pour une demande de bricolage',
                'department' => '75',
                'city' => 'Paris',
                'isFree' => false,
                'suggestedPrice' => '100.00',
            ])
        );

        $this->assertResponseStatusCodeSame(201);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Test demande', $response['title']);
    }
}

