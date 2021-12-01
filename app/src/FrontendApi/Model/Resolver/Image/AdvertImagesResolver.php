<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Image;

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
     * @return array
     */
    public function resolveByAdvert(Advert $advert, ?string $type, ?array $sizes): array
    {
        /** @var \App\Component\Image\Image[] $images */
        $images = $this->frontendApiImageFacade->getImagesByEntityIdAndNameIndexedById(
            $advert->getId(),
            self::ENTITY_NAME,
            $type
        );

        return $this->getResolvedImages(
            $images,
            $this->getSizeConfigsForAdvert($advert, $type, $sizes)
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
