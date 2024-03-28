<?php

declare(strict_types=1);

namespace App\Component\Packetery;

use App\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportType;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class PacketeryCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Component\Packetery\PacketeryClient $packeteryClient
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(
        private readonly PacketeryClient $packeteryClient,
        private readonly OrderFacade $orderFacade,
        private readonly TransportTypeFacade $transportTypeFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $transportType = $this->transportTypeFacade->getByCode(TransportType::TYPE_PACKETERY);
        $orders = $this->orderFacade->getAllWithoutTrackingNumberByTransportType($transportType);
        $this->packeteryClient->sendPackets($orders);
    }
}
