<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class PacketeryCronModule implements SimpleCronModuleInterface
{
    public function __construct(
        protected readonly PacketeryClient $packeteryClient,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function run(): void
    {
        $orders = $this->orderFacade->getAllWithoutTrackingNumberByTransportType(TransportTypeEnum::TYPE_PACKETERY);
        $this->packeteryClient->sendPackets($orders);
    }
}
