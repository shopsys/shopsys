<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery\PickupPoint;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Component\Packetery\PacketeryConfig;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class PacketeryPickupPointImportCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly PacketeryPickupPointFacade $packeteryPickupPointFacade,
        protected readonly PacketeryConfig $packeteryConfig,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function run(): void
    {
        if (!$this->packeteryConfig->isPickupPointFeedAllowed()) {
            $this->logger->info('Packetery is not enabled or the API key is not set, the pickup points are not imported.');

            return;
        }

        $importResult = $this->packeteryPickupPointFacade->importPickupPointsFromFeeds();

        $this->logger->info(sprintf(
            'Imported %d Packetery pickup points, removed %d pickup points missing in the feeds.',
            $importResult['importedCount'],
            $importResult['removedCount'],
        ));
    }
}
