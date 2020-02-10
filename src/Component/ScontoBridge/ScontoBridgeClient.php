<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ScontoBridgeClient
{
    private const URI_TOKEN_AUTH = 'TokenAuth/Authenticate';

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var \App\Component\ScontoBridge\ScontoBridgeConfig
     */
    private $scontoBridgeConfig;

    /**
     * @var string|null
     */
    private $scontoBridgeAccessToken = null;

    /**
     * @var \DateTime|null
     */
    private $scontoBridgeAccessTokenExpiration = null;

    /**
     * @param \App\Component\ScontoBridge\ScontoBridgeConfig $scontoBridgeConfig
     * @param int $timeout
     */
    public function __construct(ScontoBridgeConfig $scontoBridgeConfig, int $timeout = 120)
    {
        $this->scontoBridgeConfig = $scontoBridgeConfig;
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    private function getScontoBridgeAccessToken(): string
    {
        if ($this->scontoBridgeAccessToken !== null && new DateTime() < $this->scontoBridgeAccessTokenExpiration) {
            return $this->scontoBridgeAccessToken;
        }

        $client = new Client([
            'base_uri' => $this->scontoBridgeConfig->getBaseUri(),
            'accept' => 'text/plain',
            'timeout' => $this->timeout,
        ]);

        $response = $client->post(self::URI_TOKEN_AUTH, [
            RequestOptions::JSON => [
                'userNameOrEmailAddress' => $this->scontoBridgeConfig->getUser(),
                'password' => $this->scontoBridgeConfig->getPassword(),
                'rememberClient' => true,
            ],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->scontoBridgeAccessTokenExpiration = new DateTime('+' . $responseData['result']['expireInSeconds'] . 'seconds');
        $this->scontoBridgeAccessToken = $responseData['result']['accessToken'];

        return $this->scontoBridgeAccessToken;
    }

    /**
     * @param string $uri
     * @return array
     */
    public function get(string $uri): array
    {
        $client = new Client([
            'base_uri' => $this->scontoBridgeConfig->getBaseUri(),
            'timeout' => $this->timeout,
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $this->getScontoBridgeAccessToken(),
            'Accept' => 'application/json',
        ];

        $response = $client->get($uri, [
            'headers' => $headers,
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        return $responseData['result'];
    }
}
