<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Doctrine\ORM\EntityManagerInterface;

class PacketeryPickupPointFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PacketeryPickupPointRepository $packeteryPickupPointRepository,
        protected readonly PacketeryPickupPointFactory $packeteryPickupPointFactory,
        protected readonly PacketeryPickupPointDataFactory $packeteryPickupPointDataFactory,
        protected readonly PacketeryPickupPointFeedClient $packeteryPickupPointFeedClient,
    ) {
    }

    public function findById(string $id): ?PacketeryPickupPoint
    {
        return $this->packeteryPickupPointRepository->findById($id);
    }

    /**
     * Every feed is downloaded before anything is saved, so a failed download never removes the points of a feed
     *
     * @return array{importedCount: int, removedCount: int}
     */
    public function importPickupPointsFromFeeds(): array
    {
        $packeteryPickupPointsDataIndexedById = [];

        foreach ([PacketeryPickupPointFeedClient::FEED_BRANCH, PacketeryPickupPointFeedClient::FEED_BOX] as $feed) {
            foreach ($this->packeteryPickupPointFeedClient->getFeedItems($feed) as $feedItem) {
                $packeteryPickupPointData = $this->packeteryPickupPointDataFactory->createFromFeedItem($feedItem);
                $packeteryPickupPointsDataIndexedById[$packeteryPickupPointData->id] = $packeteryPickupPointData;
            }
        }

        $removedPacketeryPickupPointsIndexedById = $this->packeteryPickupPointRepository->getAllIndexedById();

        foreach ($packeteryPickupPointsDataIndexedById as $id => $packeteryPickupPointData) {
            $packeteryPickupPoint = $removedPacketeryPickupPointsIndexedById[$id] ?? null;

            if ($packeteryPickupPoint === null) {
                $this->em->persist($this->packeteryPickupPointFactory->create($packeteryPickupPointData));

                continue;
            }

            $packeteryPickupPoint->edit($packeteryPickupPointData);
            unset($removedPacketeryPickupPointsIndexedById[$id]);
        }

        foreach ($removedPacketeryPickupPointsIndexedById as $removedPacketeryPickupPoint) {
            $this->em->remove($removedPacketeryPickupPoint);
        }

        $this->em->flush();

        return [
            'importedCount' => count($packeteryPickupPointsDataIndexedById),
            'removedCount' => count($removedPacketeryPickupPointsIndexedById),
        ];
    }
}
