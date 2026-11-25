<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Packetery;

use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPoint;
use Shopsys\FrameworkBundle\Component\Packetery\PickupPoint\PacketeryPickupPointRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

final class PacketeryPickupPointRepositoryTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private PacketeryPickupPointRepository $packeteryPickupPointRepository;

    public function testSavedPickupPointIsFoundWithItsOpeningHours(): void
    {
        $packeteryPickupPoint = new PacketeryPickupPoint(
            '13789',
            [
                1 => ['08:00-12:00', '13:00-17:00'],
                7 => ['10:00-12:00'],
            ],
            [1 => ['09:00-17:00']],
            '2026-11-30',
            ['2026-12-24' => [], '2026-12-31' => ['08:00-12:00']],
        );

        $this->packeteryPickupPointRepository->replaceAll([$packeteryPickupPoint]);

        $this->assertEquals($packeteryPickupPoint, $this->packeteryPickupPointRepository->findById('13789'));
        $this->assertNull($this->packeteryPickupPointRepository->findById('1'));
    }

    public function testReplacingSavesOnlyNewAndChangedPickupPointsAndRemovesTheMissingOnes(): void
    {
        $this->packeteryPickupPointRepository->replaceAll([
            $this->createPacketeryPickupPoint('1', '08:00-16:00'),
            $this->createPacketeryPickupPoint('2', '08:00-16:00'),
        ]);

        $replaceResult = $this->packeteryPickupPointRepository->replaceAll([
            $this->createPacketeryPickupPoint('1', '08:00-16:00'),
            $this->createPacketeryPickupPoint('2', '08:00-18:00'),
            $this->createPacketeryPickupPoint('3', '08:00-16:00'),
        ]);

        $this->assertSame(['savedCount' => 2, 'removedCount' => 0], $replaceResult);
        $this->assertSame([1 => ['08:00-18:00']], $this->packeteryPickupPointRepository->findById('2')?->regularOpeningHours);

        $replaceResult = $this->packeteryPickupPointRepository->replaceAll([
            $this->createPacketeryPickupPoint('3', '08:00-16:00'),
        ]);

        $this->assertSame(['savedCount' => 0, 'removedCount' => 2], $replaceResult);
        $this->assertNull($this->packeteryPickupPointRepository->findById('1'));
    }

    private function createPacketeryPickupPoint(string $id, string $mondayOpeningHoursRange): PacketeryPickupPoint
    {
        return new PacketeryPickupPoint($id, [1 => [$mondayOpeningHoursRange]], null, null, []);
    }
}
