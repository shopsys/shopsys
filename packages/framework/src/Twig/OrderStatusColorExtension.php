<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class OrderStatusColorExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('order_status_color', $this->getStatusColor(...)),
        ];
    }

    protected function getStatusColor(string $statusType): string
    {
        return match ($statusType) {
            OrderStatusTypeEnum::TYPE_NEW => 'yellow',
            OrderStatusTypeEnum::TYPE_DONE => 'green',
            OrderStatusTypeEnum::TYPE_CANCELED => 'red',
            OrderStatusTypeEnum::TYPE_WITHDRAWN => 'black',
            default => 'blue',
        };
    }
}
