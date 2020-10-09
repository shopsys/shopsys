<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

class AdvertResolverMap extends ResolverMap
{
    protected const RESOLVER_ADVERT_CODE = 'AdvertCode';
    protected const RESOLVER_ADVERT_IMAGE = 'AdvertImage';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(ImageFacade $imageFacade)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Advert' => [
                self::RESOLVE_TYPE => function (Advert $advert) {
                    return $this->getResolverType($advert);
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @return string
     */
    protected function getResolverType(Advert $advert): string
    {
        $type = $advert->getType();

        if ($type === Advert::TYPE_IMAGE) {
            return static::RESOLVER_ADVERT_IMAGE;
        }

        if ($type === Advert::TYPE_CODE) {
            return static::RESOLVER_ADVERT_CODE;
        }

        throw new TypeNotImplementedException($type);
    }
}
