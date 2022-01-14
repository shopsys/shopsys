<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

use App\FrontendApi\Model\Image\ImageBatchLoadData;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

class AdvertImagesResolver extends AbstractImagesResolver implements AliasedInterface
{
    private const ENTITY_NAME = 'noticer';

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param array|null $sizes
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function resolveByAdvert(Advert $advert, ?string $type, ?array $sizes): Promise
    {
        $sizeConfigs = $this->getSizeConfigsForAdvert($advert, $type, $sizes);

        return $this->imagesBatchLoader->load(
            new ImageBatchLoadData(
                $advert->getId(),
                self::ENTITY_NAME,
                $sizeConfigs,
                $type
            )
        );
    }

    /**
     * @param \App\Model\Advert\Advert $advert
     * @param string|null $type
     * @param array|null $sizes
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig[]
     */
    private function getSizeConfigsForAdvert(Advert $advert, ?string $type, ?array $sizes): array
    {
        if ($sizes === null) {
            return $this->getSizeConfigs($type, [$advert->getPositionName(), ImageConfig::ORIGINAL_SIZE_NAME], self::ENTITY_NAME);
        }

        return $this->getSizeConfigs($type, $sizes, self::ENTITY_NAME);
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolveByAdvert' => 'advertImageResolver'];
    }
}
