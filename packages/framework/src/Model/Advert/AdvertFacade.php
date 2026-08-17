<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;

class AdvertFacade
{
    public const IMAGE_TYPE_WEB = 'web';
    public const IMAGE_TYPE_MOBILE = 'mobile';

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdvertRepository $advertRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly AdvertFactory $advertFactory,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function getById(int $advertId): Advert
    {
        return $this->advertRepository->getById($advertId);
    }

    public function create(AdvertData $advertData): Advert
    {
        $advert = $this->advertFactory->create($advertData);

        $this->em->persist($advert);
        $this->em->flush();

        $this->imageFacade->manageImages($advert, $advertData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($advert, $advertData->mobileImage, self::IMAGE_TYPE_MOBILE);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ADVERTS_QUERY_KEY_PART);

        return $advert;
    }

    public function edit(int $advertId, AdvertData $advertData): Advert
    {
        $advert = $this->advertRepository->getById($advertId);
        $advert->edit($advertData);

        $this->em->flush();

        $this->imageFacade->manageImages($advert, $advertData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($advert, $advertData->mobileImage, self::IMAGE_TYPE_MOBILE);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ADVERTS_QUERY_KEY_PART);

        return $advert;
    }

    public function delete(int $advertId): void
    {
        $advert = $this->advertRepository->getById($advertId);
        $this->em->remove($advert);
        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ADVERTS_QUERY_KEY_PART);
    }
}
