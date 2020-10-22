<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;

class AdvertPositionsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry
     */
    protected $advertPositionRegistry;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
     */
    public function __construct(AdvertPositionRegistry $advertPositionRegistry)
    {
        $this->advertPositionRegistry = $advertPositionRegistry;
    }

    /**
     * @return array
     */
    public function resolve(): array
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

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'advertPositions',
        ];
    }
}
