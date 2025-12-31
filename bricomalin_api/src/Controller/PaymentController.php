<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Payment;
use App\Entity\User;
use App\Repository\JobRequestRepository;
use App\Repository\OfferRepository;
use App\Repository\PaymentRepository;
use App\Service\CodeGeneratorService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/payments')]
class PaymentController extends AbstractController
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private JobRequestRepository $jobRequestRepository,
        private OfferRepository $offerRepository,
        private StripeService $stripeService,
        private CodeGeneratorService $codeGenerator
    ) {
    }

    #[Route('/create-intent', name: 'api_payments_create_intent', methods: ['POST'])]
    public function createIntent(Request $request): JsonResponse
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
        $jobRequestId = $data['jobRequestId'] ?? null;
        $offerId = $data['offerId'] ?? null;
        $mode = $data['mode'] ?? Payment::MODE_BEFORE;

        if (!in_array($mode, [Payment::MODE_BEFORE, Payment::MODE_AFTER])) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Mode invalide (BEFORE ou AFTER)',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $jobRequest = $this->jobRequestRepository->find($jobRequestId);
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
                    'message' => 'Vous n\'êtes pas autorisé',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        $offer = $this->offerRepository->find($offerId);
        if (!$offer || $offer->getJobRequest() !== $jobRequest) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Offre non trouvée',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($offer->getStatus() !== Offer::STATUS_ACCEPTED) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'L\'offre doit être acceptée',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si un paiement existe déjà
        $existingPayment = $this->paymentRepository->findOneBy([
            'jobRequest' => $jobRequest,
            'offer' => $offer,
        ]);

        if ($existingPayment) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_CONFLICT,
                    'message' => 'Un paiement existe déjà pour cette offre',
                ],
            ], Response::HTTP_CONFLICT);
        }

        $amount = $offer->getAmount() ?? $jobRequest->getSuggestedPrice();
        if (!$amount || (float) $amount <= 0) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Montant invalide',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Convertir en centimes
        $amountInCents = (int) round((float) $amount * 100);

        $payment = new Payment();
        $payment->setJobRequest($jobRequest);
        $payment->setOffer($offer);
        $payment->setPayer($user);
        $payment->setPayee($offer->getProposer());
        $payment->setMode($mode);
        $payment->setAmount($amount);
        $payment->setCurrency('EUR');

        try {
            if ($mode === Payment::MODE_AFTER) {
                // Mode AFTER : créer PaymentIntent avec capture manuelle
                $paymentIntent = $this->stripeService->createPaymentIntent($amountInCents, 'eur', true);
                $payment->setStripePaymentIntentId($paymentIntent->id);
                $payment->setStatus(Payment::STATUS_PENDING);

                // Générer les codes
                $payment->setAfterWorkCodeRequester($this->codeGenerator->generateCode());
                $payment->setAfterWorkCodeProposer($this->codeGenerator->generateCode());
            } else {
                // Mode BEFORE : créer PaymentIntent normal
                $paymentIntent = $this->stripeService->createPaymentIntent($amountInCents, 'eur', false);
                $payment->setStripePaymentIntentId($paymentIntent->id);
                $payment->setStatus(Payment::STATUS_REQUIRES_ACTION);
            }

            $this->paymentRepository->save($payment, true);

            return $this->json([
                'id' => $payment->getId(),
                'paymentIntentId' => $paymentIntent->id,
                'clientSecret' => $paymentIntent->client_secret,
                'mode' => $payment->getMode(),
                'status' => $payment->getStatus(),
                'amount' => $payment->getAmount(),
                'currency' => $payment->getCurrency(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Erreur Stripe: ' . $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/confirm-after-work', name: 'api_payments_confirm_after_work', methods: ['POST'])]
    public function confirmAfterWork(Request $request): JsonResponse
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
        $paymentId = $data['paymentId'] ?? null;
        $code = $data['code'] ?? null;

        if (!$paymentId || !$code) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'paymentId et code requis',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $payment = $this->paymentRepository->find($paymentId);
        if (!$payment) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_NOT_FOUND,
                    'message' => 'Paiement non trouvé',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        if ($payment->getMode() !== Payment::MODE_AFTER) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Ce paiement n\'est pas en mode AFTER',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier le code selon l'utilisateur
        $isRequester = $payment->getPayer() === $user;
        $isProposer = $payment->getPayee() === $user;

        if (!$isRequester && !$isProposer) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_FORBIDDEN,
                    'message' => 'Vous n\'êtes pas autorisé',
                ],
            ], Response::HTTP_FORBIDDEN);
        }

        $expectedCode = $isRequester 
            ? $payment->getAfterWorkCodeRequester() 
            : $payment->getAfterWorkCodeProposer();

        if ($code !== $expectedCode) {
            return $this->json([
                'error' => [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Code invalide',
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        // Marquer la validation
        if ($isRequester && !$payment->getRequesterValidatedAt()) {
            $payment->setRequesterValidatedAt(new \DateTimeImmutable());
        } elseif ($isProposer && !$payment->getProposerValidatedAt()) {
            $payment->setProposerValidatedAt(new \DateTimeImmutable());
        }

        // Si les deux codes sont validés, capturer le paiement
        if ($payment->isBothCodesValidated() && $payment->getStripePaymentIntentId()) {
            try {
                $this->stripeService->capturePaymentIntent($payment->getStripePaymentIntentId());
                $payment->setStatus(Payment::STATUS_PAID);
            } catch (\Exception $e) {
                $payment->setStatus(Payment::STATUS_FAILED);
                return $this->json([
                    'error' => [
                        'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                        'message' => 'Erreur lors de la capture: ' . $e->getMessage(),
                    ],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $this->paymentRepository->save($payment, true);

        return $this->json([
            'id' => $payment->getId(),
            'status' => $payment->getStatus(),
            'requesterValidated' => $payment->getRequesterValidatedAt() !== null,
            'proposerValidated' => $payment->getProposerValidatedAt() !== null,
            'bothValidated' => $payment->isBothCodesValidated(),
        ]);
    }

    #[Route('/me', name: 'api_me_payments', methods: ['GET'])]
    public function myPayments(): JsonResponse
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

        $payments = $this->paymentRepository->findByUser($user);

        return $this->json(array_map(function (Payment $payment) use ($user) {
            return [
                'id' => $payment->getId(),
                'mode' => $payment->getMode(),
                'status' => $payment->getStatus(),
                'amount' => $payment->getAmount(),
                'currency' => $payment->getCurrency(),
                'createdAt' => $payment->getCreatedAt()?->format('c'),
                'jobRequest' => [
                    'id' => $payment->getJobRequest()?->getId(),
                    'title' => $payment->getJobRequest()?->getTitle(),
                ],
                'isPayer' => $payment->getPayer() === $user,
                'isPayee' => $payment->getPayee() === $user,
                'requesterValidated' => $payment->getRequesterValidatedAt() !== null,
                'proposerValidated' => $payment->getProposerValidatedAt() !== null,
            ];
        }, $payments));
    }
}

