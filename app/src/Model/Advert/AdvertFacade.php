<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade as BaseAdvertFacade;

/**
 * @property \App\Model\Advert\AdvertRepository $advertRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @property \App\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Advert\AdvertRepository $advertRepository, \App\Component\Image\ImageFacade $imageFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Advert\AdvertFactoryInterface $advertFactory, \App\Model\Advert\AdvertPositionRegistry $advertPositionRegistry)
 * @method \App\Model\Advert\Advert getById(int $advertId)
 * @method \App\Model\Advert\Advert|null findRandomAdvertByPositionOnCurrentDomain(string $positionName)
 */
class AdvertFacade extends BaseAdvertFacade
{
    public const IMAGE_TYPE_WEB = 'web';
    public const IMAGE_TYPE_MOBILE = 'mobile';

    /**
     * @param \App\Model\Advert\AdvertData $advertData
     * @return \App\Model\Advert\Advert
     */
    public function create(AdvertData $advertData)
    {
        /** @var \App\Model\Advert\Advert $advert */
        $advert = $this->advertFactory->create($advertData);

        $this->em->persist($advert);
        $this->em->flush();
        $this->imageFacade->manageImages($advert, $advertData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($advert, $advertData->mobileImage, self::IMAGE_TYPE_MOBILE);

        return $advert;
    }

    /**
     * @param int $advertId
     * @param \App\Model\Advert\AdvertData $advertData
     * @return \App\Model\Advert\Advert
     */
    public function edit($advertId, AdvertData $advertData)
    {
        /** @var \App\Model\Advert\Advert $advert */
        $advert = $this->advertRepository->getById($advertId);
        $advert->edit($advertData);

        $this->em->flush();
        $this->imageFacade->manageImages($advert, $advertData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($advert, $advertData->mobileImage, self::IMAGE_TYPE_MOBILE);

        return $advert;
    }
}
