<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class AdvertPositionsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly AdvertPositionRegistry $advertPositionRegistry,
    ) {
    }

    public function advertPositionsQuery(): array
    {
        $serialized = [];

        foreach ($this->advertPositionRegistry->getAllLabelsIndexedByNames() as $positionName => $description) {
            $serialized[] = [
                'description' => $description,
                'positionName' => $positionName,
            ];
        }

        return $serialized;
    }
}
