<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Shopsys\FrameworkBundle\Component\Packetery\PacketeryConfig;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://docs.packeta.com/docs/pudo-delivery/packeta-pudos
 */
class PacketeryPickupPointFeedClient
{
    public const string FEED_BRANCH = 'branch';
    public const string FEED_BOX = 'box';

    protected const string FEED_URL_PATTERN = 'https://pickup-point.api.packeta.com/v5/%s/%s/json';

    public function __construct(
        protected readonly PacketeryConfig $packeteryConfig,
        protected readonly HttpClientInterface $client,
    ) {
    }

    /**
     * Returns the points of all the countries allowed for the API key
     *
     * @return array<int, array<string, mixed>>
     */
    public function getFeedItems(string $feed): array
    {
        $apiKey = $this->packeteryConfig->getApiKey();

        $response = $this->client->request(
            'GET',
            sprintf(static::FEED_URL_PATTERN, $apiKey, $feed),
            [
                // the client negotiates and decodes the compression itself (the feed has tens of megabytes uncompressed)
                'headers' => [
                    'X-Api-Key' => $apiKey,
                ],
            ],
        );

        return $response->toArray();
    }
}
