<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintActualResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;

class ComplaintResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
        protected readonly ComplaintActualResolutionEnum $complaintActualResolutionEnum,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'Complaint' => [
                'status' => fn (Complaint $complaint) => $complaint->getStatus()->getName($this->domain->getLocale()),
                'resolution' => fn (Complaint $complaint) => $this->complaintResolutionEnum->serialize()[$complaint->getResolution()],
                'actualResolution' => fn (Complaint $complaint) => $this->serializeActualResolution($complaint),
            ],
        ];
    }

    /**
     * @return array{name: string, value: string}|null
     */
    protected function serializeActualResolution(Complaint $complaint): ?array
    {
        $actualResolution = $complaint->getActualResolution();

        if ($actualResolution === null) {
            return null;
        }

        return $this->complaintActualResolutionEnum->serialize()[$actualResolution];
    }
}
