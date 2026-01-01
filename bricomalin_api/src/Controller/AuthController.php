<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setDisplayName($data['displayName'] ?? null);
        $user->setPhone($data['phone'] ?? null);

        $password = $data['password'] ?? '';
        if (strlen($password) < 6) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Le mot de passe doit contenir au moins 6 caractères',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPasswordHash($hashedPassword);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $details = [];
            foreach ($errors as $error) {
                $details[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Erreur de validation',
                    'details' => $details,
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'email existe déjà
        if ($this->userRepository->findOneBy(['email' => $user->getEmail()])) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_CONFLICT,
                    'message' => 'Cet email est déjà utilisé',
                ],
            ], Response::HTTP_CONFLICT);
        }

        $this->userRepository->save($user, true);

        return $this->json([
            'message' => 'Inscription réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'displayName' => $user->getDisplayName(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Non authentifié',
                ],
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'displayName' => $user->getDisplayName(),
            'phone' => $user->getPhone(),
            'isPro' => $user->isPro(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
        ]);
    }
}

