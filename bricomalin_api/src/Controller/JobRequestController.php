<?php

namespace App\Controller;

use App\Entity\JobRequest;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\JobRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/job-requests')]
class JobRequestController extends AbstractController
{
    public function __construct(
        private JobRequestRepository $jobRequestRepository,
        private CategoryRepository $categoryRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'api_job_requests_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $department = $request->query->get('department');
        $categoryId = $request->query->get('category') ? (int) $request->query->get('category') : null;
        $q = $request->query->get('q');
        $status = $request->query->get('status', JobRequest::STATUS_OPEN);

        $jobRequests = $this->jobRequestRepository->findByFilters($department, $categoryId, $q, $status);

        return $this->json(array_map(function (JobRequest $jr) {
            return $this->serializeJobRequest($jr);
        }, $jobRequests));
    }

    #[Route('', name: 'api_job_requests_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
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

        $category = $this->categoryRepository->find($data['categoryId'] ?? 0);
        if (!$category) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Catégorie invalide',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $jobRequest = new JobRequest();
        $jobRequest->setRequester($user);
        $jobRequest->setCategory($category);
        $jobRequest->setTitle($data['title'] ?? '');
        $jobRequest->setDescription($data['description'] ?? '');
        $jobRequest->setDepartment($data['department'] ?? '');
        $jobRequest->setCity($data['city'] ?? null);
        $jobRequest->setIsFree($data['isFree'] ?? false);
        $jobRequest->setSuggestedPrice($data['suggestedPrice'] ?? null);

        $errors = $this->validator->validate($jobRequest);
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

        $this->jobRequestRepository->save($jobRequest, true);

        return $this->json($this->serializeJobRequest($jobRequest), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_job_requests_get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $jobRequest = $this->jobRequestRepository->find($id);
        if (!$jobRequest) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Demande non trouvée',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serializeJobRequest($jobRequest, true));
    }

    #[Route('/{id}', name: 'api_job_requests_update', methods: ['PATCH'])]
    public function update(int $id, Request $request): JsonResponse
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

        $jobRequest = $this->jobRequestRepository->find($id);
        if (!$jobRequest) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Demande non trouvée',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($jobRequest->getRequester() !== $user) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => 'Vous n\'êtes pas autorisé à modifier cette demande',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) {
            $jobRequest->setStatus($data['status']);
        }

        if (isset($data['title'])) {
            $jobRequest->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $jobRequest->setDescription($data['description']);
        }

        $this->jobRequestRepository->save($jobRequest, true);

        return $this->json($this->serializeJobRequest($jobRequest));
    }

    private function serializeJobRequest(JobRequest $jr, bool $detailed = false): array
    {
        $data = [
            'id' => $jr->getId(),
            'title' => $jr->getTitle(),
            'description' => $jr->getDescription(),
            'department' => $jr->getDepartment(),
            'city' => $jr->getCity(),
            'isFree' => $jr->isFree(),
            'suggestedPrice' => $jr->getSuggestedPrice(),
            'status' => $jr->getStatus(),
            'createdAt' => $jr->getCreatedAt()?->format('c'),
            'category' => [
                'id' => $jr->getCategory()?->getId(),
                'name' => $jr->getCategory()?->getName(),
                'key' => $jr->getCategory()?->getKey(),
            ],
        ];

        if ($detailed) {
            $data['requester'] = [
                'id' => $jr->getRequester()?->getId(),
                'displayName' => $jr->getRequester()?->getDisplayName(),
                'isPro' => $jr->getRequester()?->isPro(),
            ];
        }

        return $data;
    }
}

