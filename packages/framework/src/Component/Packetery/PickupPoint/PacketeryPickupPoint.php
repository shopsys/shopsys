<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * A local copy of a Packeta pickup point (a branch or a Z-BOX) imported from the Packeta pickup point feeds,
 * so the expected delivery date can respect the opening hours of the point selected in the cart;
 * every opening hours range is a "HH:MM-HH:MM" string
 */
#[AsMcpTable]
#[ORM\Table(name: 'packetery_pickup_points')]
#[ORM\Entity]
class PacketeryPickupPoint
{
    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    protected $id;

    /**
     * @var array<int, string[]>
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'json')]
    protected $regularOpeningHours;

    /**
     * @var array<int, string[]>|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'json', nullable: true)]
    protected $upcomingOpeningHours;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    protected $upcomingOpeningHoursSince;

    /**
     * @var array<string, string[]>
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'json')]
    protected $exceptionOpeningHours;

    public function __construct(PacketeryPickupPointData $packeteryPickupPointData)
    {
        $this->id = $packeteryPickupPointData->id;
        $this->setData($packeteryPickupPointData);
    }

    public function edit(PacketeryPickupPointData $packeteryPickupPointData): void
    {
        $this->setData($packeteryPickupPointData);
    }

    protected function setData(PacketeryPickupPointData $packeteryPickupPointData): void
    {
        $this->regularOpeningHours = $packeteryPickupPointData->regularOpeningHours;
        $this->upcomingOpeningHours = $packeteryPickupPointData->upcomingOpeningHours;
        $this->exceptionOpeningHours = $packeteryPickupPointData->exceptionOpeningHours;

        // Doctrine compares dates by instance, so an equal date is kept to not update every point on each import
        if ($this->upcomingOpeningHoursSince?->format('Y-m-d') !== $packeteryPickupPointData->upcomingOpeningHoursSince?->format('Y-m-d')) {
            $this->upcomingOpeningHoursSince = $packeteryPickupPointData->upcomingOpeningHoursSince;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Ranges indexed by the ISO-8601 day of week (1 = Monday, 7 = Sunday); a missing day means closed
     *
     * @return array<int, string[]>
     */
    public function getRegularOpeningHours()
    {
        return $this->regularOpeningHours;
    }

    /**
     * New regular opening hours valid since getUpcomingOpeningHoursSince(), indexed the same way as the regular ones
     *
     * @return array<int, string[]>|null
     */
    public function getUpcomingOpeningHours()
    {
        return $this->upcomingOpeningHours;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpcomingOpeningHoursSince()
    {
        return $this->upcomingOpeningHoursSince;
    }

    /**
     * Ranges indexed by a "Y-m-d" date overriding the regular opening hours; an empty list means closed
     *
     * @return array<string, string[]>
     */
    public function getExceptionOpeningHours()
    {
        return $this->exceptionOpeningHours;
    }
}
