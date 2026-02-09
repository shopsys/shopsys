<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;

class ComplaintResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Complaint' => [
                'status' => fn (Complaint $complaint) => $complaint->getStatus()->getName($this->domain->getLocale()),
                'resolution' => fn (Complaint $complaint) => $this->complaintResolutionEnum->serialize()[$complaint->getResolution()],
            ],
        ];
    }
}
