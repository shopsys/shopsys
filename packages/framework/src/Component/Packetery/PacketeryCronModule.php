<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery;

use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportType;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class PacketeryCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Packetery\PacketeryClient $packeteryClient
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(
        protected readonly PacketeryClient $packeteryClient,
        protected readonly OrderFacade $orderFacade,
        protected readonly TransportTypeFacade $transportTypeFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $transportType = $this->transportTypeFacade->getByCode(TransportType::TYPE_PACKETERY);
        $orders = $this->orderFacade->getAllWithoutTrackingNumberByTransportType($transportType);
        $this->packeteryClient->sendPackets($orders);
    }
}
