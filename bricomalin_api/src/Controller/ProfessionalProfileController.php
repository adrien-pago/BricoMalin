<?php

namespace App\Controller;

use App\Entity\ProfessionalProfile;
use App\Entity\User;
use App\Repository\ProfessionalProfileRepository;
use App\Service\FileUploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/profiles')]
class ProfessionalProfileController extends AbstractController
{
    public function __construct(
        private ProfessionalProfileRepository $profileRepository,
        private FileUploadService $fileUploadService,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/start', name: 'api_profiles_start', methods: ['POST'])]
    public function start(Request $request): JsonResponse
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

        $data = json_decode($request->getContent(), true);
        $siret = $data['siret'] ?? null;

        if (!$siret) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'SIRET requis',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $profile = $user->getProfessionalProfile();
        if (!$profile) {
            $profile = new ProfessionalProfile();
            $profile->setUser($user);
        }

        $profile->setSiret($siret);
        $profile->setStatus(ProfessionalProfile::STATUS_PENDING);

        $errors = $this->validator->validate($profile);
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

        $this->profileRepository->save($profile, true);

        return $this->json([
            'id' => $profile->getId(),
            'siret' => $profile->getSiret(),
            'status' => $profile->getStatus(),
            'createdAt' => $profile->getCreatedAt()?->format('c'),
        ]);
    }

    #[Route('/upload-id', name: 'api_profiles_upload_id', methods: ['POST'])]
    public function uploadId(Request $request): JsonResponse
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

        $profile = $user->getProfessionalProfile();
        if (!$profile) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Vous devez d\'abord renseigner votre SIRET',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $file = $request->files->get('idDocument');
        if (!$file) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Fichier requis',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier le type de fichier (PDF, JPG, PNG)
        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Format de fichier non autorisé (PDF, JPG, PNG uniquement)',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $relativePath = $this->fileUploadService->upload($file, 'id-documents');
            $profile->setIdDocumentPath($relativePath);
            $this->profileRepository->save($profile, true);

            return $this->json([
                'id' => $profile->getId(),
                'idDocumentPath' => $relativePath,
                'status' => $profile->getStatus(),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Erreur lors de l\'upload: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/me', name: 'api_profiles_me', methods: ['GET'])]
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

        $profile = $user->getProfessionalProfile();
        if (!$profile) {
            return $this->json([
                'status' => null,
            ]);
        }

        return $this->json([
            'id' => $profile->getId(),
            'siret' => $profile->getSiret(),
            'status' => $profile->getStatus(),
            'hasIdDocument' => $profile->getIdDocumentPath() !== null,
            'createdAt' => $profile->getCreatedAt()?->format('c'),
            'updatedAt' => $profile->getUpdatedAt()?->format('c'),
        ]);
    }
}

