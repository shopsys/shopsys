<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayNotConfiguredException;
use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use GoPay\Definition\RequestMethods;
use GoPay\GoPay;
use GoPay\Http\JsonBrowser;
use GoPay\Http\Log\NullLogger;
use GoPay\Http\Response;
use GoPay\OAuth2;
use GoPay\Token\CachedOAuth;
use GoPay\Token\InMemoryTokenCache;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class GoPayClient
{
    public const RESPONSE_STATUS_CODE_OK = 200;

    private GoPay $goPay;

    private CachedOAuth $oAuth;

    /**
     * @param array $config
     */
    public function __construct(private array $config)
    {
        $browser = new JsonBrowser(new NullLogger(), $this->config['timeout']);
        $this->goPay = new GoPay($this->config, $browser);
        $this->oAuth = new CachedOAuth(new OAuth2($this->goPay), new InMemoryTokenCache());
    }

    /**
     * @param string $urlPath
     * @param string $contentType
     * @param string $method
     * @param array|null $data
     * @return \GoPay\Http\Response
     */
    private function sendApiRequest(
        string $urlPath,
        string $contentType,
        string $method,
        ?array $data = null,
    ): Response {
        if ($this->config['goid'] === null) {
            throw new GoPayNotConfiguredException();
        }

        $token = $this->oAuth->authorize();

        if ($token->token) {
            return $this->goPay->call(
                $urlPath,
                $contentType,
                'Bearer ' . $token->token,
                $method,
                $data,
            );
        }

        return $token->response;
    }

    /**
     * @param array $rawPayment
     * @return \GoPay\Http\Response
     */
    public function sendPaymentToGoPay(array $rawPayment): Response
    {
        $payment = $rawPayment + [
            'target' => [
                'type' => 'ACCOUNT',
                'goid' => (string)$this->goPay->getConfig('goid'),
            ],
            'lang' => $this->goPay->getConfig('language'),
        ];

        return $this->sendApiRequest(
            'payments/payment',
            GoPay::JSON,
            RequestMethods::POST,
            $payment,
        );
    }

    /**
     * @param string $id
     * @return \GoPay\Http\Response
     */
    public function getStatus(string $id): Response
    {
        $urlPath = 'payments/payment/' . $id;

        $response = $this->sendApiRequest($urlPath, GoPay::FORM, RequestMethods::GET);

        if ((int)$response->statusCode !== (int)self::RESPONSE_STATUS_CODE_OK) {
            throw new GoPayPaymentDownloadException(
                $this->goPay->buildUrl('api/' . $urlPath),
                RequestMethods::GET,
                null,
                $response,
                self::RESPONSE_STATUS_CODE_OK,
                $response->statusCode,
            );
        }

        return $response;
    }

    /**
     * @param string $id
     * @param int $amount
     * @return \GoPay\Http\Response
     */
    public function refundTransaction(string $id, int $amount): Response
    {
        $urlPath = 'payments/payment/' . $id . '/refund';
        $body = [
            'amount' => $amount,
        ];

        $response = $this->sendApiRequest(
            $urlPath,
            GoPay::FORM,
            RequestMethods::POST,
            $body,
        );

        if ((int)$response->statusCode !== (int)self::RESPONSE_STATUS_CODE_OK) {
            throw new GoPayPaymentDownloadException(
                $this->goPay->buildUrl('api/' . $urlPath),
                RequestMethods::POST,
                $body,
                $response,
                self::RESPONSE_STATUS_CODE_OK,
                $response->statusCode,
            );
        }

        return $response;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return array
     */
    public function downloadGoPayPaymentMethodsByCurrency(Currency $currency): array
    {
        $urlPath = 'eshops/eshop/' . $this->goPay->getConfig('goid') . '/payment-instruments/' . $currency->getCode();

        $response = $this->sendApiRequest(
            $urlPath,
            GoPay::FORM,
            RequestMethods::GET,
        );

        if ((int)$response->statusCode !== (int)self::RESPONSE_STATUS_CODE_OK) {
            throw new GoPayPaymentDownloadException(
                $this->goPay->buildUrl('api/' . $urlPath),
                RequestMethods::GET,
                null,
                $response,
                self::RESPONSE_STATUS_CODE_OK,
                $response->statusCode,
            );
        }

        return $response->json['enabledPaymentInstruments'];
    }

    /**
     * @return string
     */
    public function urlToEmbedJs(): string
    {
        return $this->goPay->buildEmbedUrl();
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->config['language'];
    }
}
