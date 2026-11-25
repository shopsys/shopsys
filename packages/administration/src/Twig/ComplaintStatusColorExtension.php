<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusTypeEnum;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ComplaintStatusColorExtension extends AbstractExtension
{
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('complaint_status_color', $this->getStatusColor(...)),
        ];
    }

    protected function getStatusColor(string $statusType): string
    {
        return match ($statusType) {
            ComplaintStatusTypeEnum::STATUS_TYPE_NEW => 'yellow',
            ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED => 'green',
            ComplaintStatusTypeEnum::STATUS_TYPE_RESOLVED_CLAIMABLE_AGAIN => 'lime',
            default => 'blue',
        };
    }
}
