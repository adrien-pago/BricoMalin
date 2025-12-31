<?php

namespace App\Service;

use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeService
{
    private StripeClient $stripe;

    public function __construct(string $secretKey)
    {
        $this->stripe = new StripeClient($secretKey);
    }

    /**
     * Crée un PaymentIntent pour un paiement
     *
     * @param int $amount Montant en centimes
     * @param string $currency Devise (EUR par défaut)
     * @param bool $manualCapture Si true, capture manuelle (pour mode AFTER)
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function createPaymentIntent(int $amount, string $currency = 'eur', bool $manualCapture = false): PaymentIntent
    {
        $params = [
            'amount' => $amount,
            'currency' => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ];

        if ($manualCapture) {
            $params['capture_method'] = 'manual';
        }

        return $this->stripe->paymentIntents->create($params);
    }

    /**
     * Capture un PaymentIntent (pour mode AFTER)
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function capturePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->capture($paymentIntentId);
    }

    /**
     * Récupère un PaymentIntent
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }
}

