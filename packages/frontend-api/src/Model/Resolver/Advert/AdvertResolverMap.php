<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Advert\Advert;

class AdvertResolverMap extends ResolverMap
{
    protected const string RESOLVER_ADVERT_CODE = 'AdvertCode';
    protected const string RESOLVER_ADVERT_IMAGE = 'AdvertImage';
    protected const string RESOLVER_CATEGORIES_FIELD = 'categories';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Overblog\DataLoader\DataLoaderInterface $categoriesBatchLoader
     */
    public function __construct(
        protected readonly ImageFacade $imageFacade,
        protected readonly DataLoaderInterface $categoriesBatchLoader,
    ) {
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        $commonAdvertResolverFields = [
            self::RESOLVER_CATEGORIES_FIELD => $this->mapVisibleCategories(...),
        ];

        return [
            'Advert' => [
                self::RESOLVE_TYPE => function (Advert $advert) {
                    return $this->getResolverType($advert);
                },
            ],
            self::RESOLVER_ADVERT_CODE => $commonAdvertResolverFields,
            self::RESOLVER_ADVERT_IMAGE => $commonAdvertResolverFields,
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @return \GraphQL\Executor\Promise\Promise
     */
    protected function mapVisibleCategories(Advert $advert): Promise
    {
        return $this->categoriesBatchLoader->load($advert->getCategoryIds());
    }
}
