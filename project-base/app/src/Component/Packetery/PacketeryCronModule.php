<?php

declare(strict_types=1);

namespace App\Component\Packetery;

use App\Model\Order\OrderFacade;
use App\Model\Transport\Type\TransportTypeEnum;
use App\Model\Transport\Type\TransportTypeFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class PacketeryCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Component\Packetery\PacketeryClient
     */
    private PacketeryClient $packeteryClient;

    /**
     * @var \App\Model\Order\OrderFacade
     */
    private OrderFacade $orderFacade;

    /**
     * @var \App\Model\Transport\Type\TransportTypeFacade
     */
    private TransportTypeFacade $transportTypeFacade;

    /**
     * @param \App\Component\Packetery\PacketeryClient $packeteryClient
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(PacketeryClient $packeteryClient, OrderFacade $orderFacade, TransportTypeFacade $transportTypeFacade)
    {
        $this->packeteryClient = $packeteryClient;
        $this->orderFacade = $orderFacade;
        $this->transportTypeFacade = $transportTypeFacade;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $transportType = $this->transportTypeFacade->getByCode(TransportTypeEnum::TYPE_PACKETERY);
        $orders = $this->orderFacade->getAllWithoutTrackingNumberByTransportType($transportType);
        $this->packeteryClient->sendPackets($orders);
    }
}
