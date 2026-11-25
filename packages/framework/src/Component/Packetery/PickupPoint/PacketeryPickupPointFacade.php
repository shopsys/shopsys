<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;

class PacketeryPickupPointFacade
{
    protected const string PICKUP_POINTS_CACHE_NAMESPACE = 'packeteryPickupPoints';

    public function __construct(
        protected readonly PacketeryPickupPointRepository $packeteryPickupPointRepository,
        protected readonly PacketeryPickupPointFeedClient $packeteryPickupPointFeedClient,
        protected readonly PacketeryPickupPointFeedParser $packeteryPickupPointFeedParser,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * The point is cached for the request, as the delivery date is calculated repeatedly (transport list, cart, order)
     */
    public function findById(string $id): ?PacketeryPickupPoint
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PICKUP_POINTS_CACHE_NAMESPACE,
            fn (): ?PacketeryPickupPoint => $this->packeteryPickupPointRepository->findById($id),
            $id,
        );
    }

    /**
     * Every feed is downloaded before anything is saved, so a failed download never removes the points of a feed
     *
     * @return array{importedCount: int, savedCount: int, removedCount: int}
     */
    public function importPickupPointsFromFeeds(): array
    {
        $packeteryPickupPointsIndexedById = [];

        foreach ([PacketeryPickupPointFeedClient::FEED_BRANCH, PacketeryPickupPointFeedClient::FEED_BOX] as $feed) {
            foreach ($this->packeteryPickupPointFeedClient->getFeedItems($feed) as $feedItem) {
                $packeteryPickupPoint = $this->packeteryPickupPointFeedParser->parseFeedItem($feedItem);
                $packeteryPickupPointsIndexedById[$packeteryPickupPoint->id] = $packeteryPickupPoint;
            }
        }

        return [
            'importedCount' => count($packeteryPickupPointsIndexedById),
            ...$this->packeteryPickupPointRepository->replaceAll(array_values($packeteryPickupPointsIndexedById)),
        ];
    }
}
