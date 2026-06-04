<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use GoPay\Http\Response;
use Override;
use Shopsys\FrameworkBundle\Model\GoPay\GoPayClient as BaseGoPayClient;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class GoPayClient extends BaseGoPayClient
{
    /**
     * @var array<string, mixed>|null
     */
    public static ?array $lastRawPayment = null;

    #[Override]
    public function sendPaymentToGoPay(array $rawPayment): Response
    {
        self::$lastRawPayment = $rawPayment;

        $response = new Response();
        $response->json = [
            'gw_url' => 'https://example.com?supertoken=xyz123456',
            'id' => '987654321',
            'state' => 'CREATED',
            'payment_instrument' => 'BANK_ACCOUNT',
        ];
        $response->statusCode = 200;

        return $response;
    }

    #[Override]
    public function getStatus(string $id): Response
    {
        $response = new Response();
        $response->json = [
            'state' => 'PAID',
            'id' => $id,
        ];
        $response->statusCode = 200;

        return $response;
    }

    #[Override]
    public function refundTransaction(string $id, int $amount): Response
    {
        $response = new Response();
        $response->json = [
            'id' => '987654321',
            'result' => 'FINISHED',
        ];
        $response->statusCode = 200;

        return $response;
    }

    #[Override]
    public function downloadGoPayPaymentMethodsByCurrency(Currency $currency): array
    {
        return [];
    }
}
