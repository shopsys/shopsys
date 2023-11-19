<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment\GoPay;

use App\Model\GoPay\GoPayClient as BaseGoPayClient;
use GoPay\Http\Response;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class GoPayClient extends BaseGoPayClient
{
    /**
     * @param mixed[] $rawPayment
     * @return \GoPay\Http\Response
     */
    public function sendPaymentToGoPay(array $rawPayment): Response
    {
        $response = new Response();
        $response->json = [
            'gw_url' => 'https://example.com?supertoken=xyz123456',
            'id' => '987654321',
            'state' => 'CREATED',
        ];
        $response->statusCode = 200;

        return $response;
    }

    /**
     * @param string $id
     * @return \GoPay\Http\Response
     */
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

    /**
     * @param string $id
     * @param int $amount
     * @return \GoPay\Http\Response
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return mixed[]
     */
    public function downloadGoPayPaymentMethodsByCurrency(Currency $currency): array
    {
        return [];
    }
}
