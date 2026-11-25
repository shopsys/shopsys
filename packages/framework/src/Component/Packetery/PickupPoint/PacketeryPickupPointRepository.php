<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * The points are a plain copy of the Packeta feeds read only by their id, so they are stored by SQL without an entity
 */
class PacketeryPickupPointRepository
{
    /**
     * Every point has 5 parameters, so a batch stays well below the PostgreSQL limit of 65535 parameters per query
     */
    protected const int UPSERT_BATCH_SIZE = 1000;

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function findById(string $id): ?PacketeryPickupPoint
    {
        $row = $this->em->getConnection()->fetchAssociative(
            'SELECT id, regular_opening_hours, upcoming_opening_hours, upcoming_opening_hours_since, exception_opening_hours
            FROM packetery_pickup_points
            WHERE id = :id',
            ['id' => $id],
        );

        if ($row === false) {
            return null;
        }

        return new PacketeryPickupPoint(
            $row['id'],
            json_decode($row['regular_opening_hours'], true, flags: JSON_THROW_ON_ERROR),
            $row['upcoming_opening_hours'] === null ? null : json_decode($row['upcoming_opening_hours'], true, flags: JSON_THROW_ON_ERROR),
            $row['upcoming_opening_hours_since'],
            json_decode($row['exception_opening_hours'], true, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * Saves the given points and removes all the others, so the table mirrors the feeds
     *
     * @param \Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPoint[] $packeteryPickupPoints
     * @return array{savedCount: int, removedCount: int} saved are only the new and the changed points
     */
    public function replaceAll(array $packeteryPickupPoints): array
    {
        return $this->em->getConnection()->transactional(function (Connection $connection) use ($packeteryPickupPoints): array {
            $savedCount = 0;

            foreach (array_chunk($packeteryPickupPoints, static::UPSERT_BATCH_SIZE) as $packeteryPickupPointsBatch) {
                $savedCount += $this->upsertBatch($connection, $packeteryPickupPointsBatch);
            }

            $removedCount = $connection->executeStatement(
                'DELETE FROM packetery_pickup_points WHERE id NOT IN (:ids)',
                ['ids' => array_map(static fn (PacketeryPickupPoint $packeteryPickupPoint): string => $packeteryPickupPoint->id, $packeteryPickupPoints)],
                ['ids' => ArrayParameterType::STRING],
            );

            return [
                'savedCount' => $savedCount,
                'removedCount' => (int)$removedCount,
            ];
        });
    }

    /**
     * An unchanged point is not updated at all, so the regular import rewrites just the few changed rows
     *
     * @param \Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPoint[] $packeteryPickupPoints
     */
    protected function upsertBatch(Connection $connection, array $packeteryPickupPoints): int
    {
        $parameters = [];

        foreach ($packeteryPickupPoints as $packeteryPickupPoint) {
            $parameters[] = $packeteryPickupPoint->id;
            $parameters[] = json_encode($packeteryPickupPoint->regularOpeningHours, JSON_THROW_ON_ERROR);
            $parameters[] = $packeteryPickupPoint->upcomingOpeningHours === null ? null : json_encode($packeteryPickupPoint->upcomingOpeningHours, JSON_THROW_ON_ERROR);
            $parameters[] = $packeteryPickupPoint->upcomingOpeningHoursSince;
            $parameters[] = json_encode($packeteryPickupPoint->exceptionOpeningHours, JSON_THROW_ON_ERROR);
        }

        $values = implode(', ', array_fill(0, count($packeteryPickupPoints), '(?, ?, ?, ?, ?)'));

        return (int)$connection->executeStatement(
            'INSERT INTO packetery_pickup_points (id, regular_opening_hours, upcoming_opening_hours, upcoming_opening_hours_since, exception_opening_hours)
            VALUES ' . $values . '
            ON CONFLICT (id) DO UPDATE SET
                regular_opening_hours = EXCLUDED.regular_opening_hours,
                upcoming_opening_hours = EXCLUDED.upcoming_opening_hours,
                upcoming_opening_hours_since = EXCLUDED.upcoming_opening_hours_since,
                exception_opening_hours = EXCLUDED.exception_opening_hours
            WHERE (
                packetery_pickup_points.regular_opening_hours,
                packetery_pickup_points.upcoming_opening_hours,
                packetery_pickup_points.upcoming_opening_hours_since,
                packetery_pickup_points.exception_opening_hours
            ) IS DISTINCT FROM (
                EXCLUDED.regular_opening_hours,
                EXCLUDED.upcoming_opening_hours,
                EXCLUDED.upcoming_opening_hours_since,
                EXCLUDED.exception_opening_hours
            )',
            $parameters,
        );
    }
}
