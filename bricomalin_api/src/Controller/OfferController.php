<?php

namespace App\Controller;

use App\Entity\JobRequest;
use App\Entity\Offer;
use App\Entity\User;
use App\Repository\JobRequestRepository;
use App\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class OfferController extends AbstractController
{
    public function __construct(
        private OfferRepository $offerRepository,
        private JobRequestRepository $jobRequestRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/job-requests/{id}/offers', name: 'api_offers_create', methods: ['POST'])]
    public function create(int $id, Request $request): JsonResponse
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

        if ($jobRequest->getStatus() !== JobRequest::STATUS_OPEN) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Cette demande n\'accepte plus d\'offres',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        $offer = new Offer();
        $offer->setJobRequest($jobRequest);
        $offer->setProposer($user);
        $offer->setAmount($data['amount'] ?? null);
        $offer->setMessage($data['message'] ?? null);

        $errors = $this->validator->validate($offer);
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

        $this->offerRepository->save($offer, true);

        return $this->json($this->serializeOffer($offer), Response::HTTP_CREATED);
    }

    #[Route('/me/offers', name: 'api_me_offers', methods: ['GET'])]
    public function myOffers(): JsonResponse
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

        $offers = $this->offerRepository->findByUser($user);

        return $this->json(array_map(function (Offer $offer) {
            return $this->serializeOffer($offer, true);
        }, $offers));
    }

    #[Route('/offers/{id}/accept', name: 'api_offers_accept', methods: ['POST'])]
    public function accept(int $id): JsonResponse
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

        $offer = $this->offerRepository->find($id);
        if (!$offer) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Offre non trouvée',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($offer->getJobRequest()->getRequester() !== $user) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => 'Vous n\'êtes pas autorisé à accepter cette offre',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        if ($offer->getStatus() !== Offer::STATUS_PENDING) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Cette offre ne peut plus être acceptée',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $offer->setStatus(Offer::STATUS_ACCEPTED);
        $offer->getJobRequest()->setStatus(JobRequest::STATUS_ASSIGNED);

        $this->offerRepository->save($offer, true);
        $this->jobRequestRepository->save($offer->getJobRequest(), true);

        return $this->json($this->serializeOffer($offer, true));
    }

    #[Route('/offers/{id}/reject', name: 'api_offers_reject', methods: ['POST'])]
    public function reject(int $id): JsonResponse
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

        $offer = $this->offerRepository->find($id);
        if (!$offer) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Offre non trouvée',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($offer->getJobRequest()->getRequester() !== $user) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => 'Vous n\'êtes pas autorisé à rejeter cette offre',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        $offer->setStatus(Offer::STATUS_REJECTED);
        $this->offerRepository->save($offer, true);

        return $this->json($this->serializeOffer($offer));
    }

    private function serializeOffer(Offer $offer, bool $detailed = false): array
    {
        $data = [
            'id' => $offer->getId(),
            'amount' => $offer->getAmount(),
            'message' => $offer->getMessage(),
            'status' => $offer->getStatus(),
            'createdAt' => $offer->getCreatedAt()?->format('c'),
            'proposer' => [
                'id' => $offer->getProposer()?->getId(),
                'displayName' => $offer->getProposer()?->getDisplayName(),
                'isPro' => $offer->getProposer()?->isPro(),
            ],
        ];

        if ($detailed) {
            $data['jobRequest'] = [
                'id' => $offer->getJobRequest()?->getId(),
                'title' => $offer->getJobRequest()?->getTitle(),
                'category' => [
                    'id' => $offer->getJobRequest()?->getCategory()?->getId(),
                    'name' => $offer->getJobRequest()?->getCategory()?->getName(),
                ],
            ];
        }

        return $data;
    }
}

