<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertFacade
{
    public const IMAGE_TYPE_WEB = 'web';
    public const IMAGE_TYPE_MOBILE = 'mobile';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertRepository $advertRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertFactoryInterface $advertFactory
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdvertRepository $advertRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly AdvertFactoryInterface $advertFactory,
        protected readonly AdvertPositionRegistry $advertPositionRegistry,
    ) {
    }

    /**
     * @param int $advertId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function getById(int $advertId): Advert
    {
        return $this->advertRepository->getById($advertId);
    }

    /**
     * @param string $positionName
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPositionOnCurrentDomain(
        string $positionName,
        ?Category $category = null,
    ): ?Advert {
        $this->advertPositionRegistry->assertPositionNameIsKnown($positionName);

        return $this->advertRepository->findRandomAdvertByPosition($positionName, $this->domain->getId(), $category);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function create(AdvertData $advertData): Advert
    {
        $advert = $this->advertFactory->create($advertData);

        $this->em->persist($advert);
        $this->em->flush();

        $this->imageFacade->manageImages($advert, $advertData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($advert, $advertData->mobileImage, self::IMAGE_TYPE_MOBILE);
        $this->em->flush();

        return $advert;
    }

    /**
     * @param int $advertId
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function edit(int $advertId, AdvertData $advertData): Advert
    {
        $advert = $this->advertRepository->getById($advertId);
        $advert->edit($advertData);

        $this->em->flush();

        $this->imageFacade->manageImages($advert, $advertData->image, self::IMAGE_TYPE_WEB);
        $this->imageFacade->manageImages($advert, $advertData->mobileImage, self::IMAGE_TYPE_MOBILE);
        $this->em->flush();

        return $advert;
    }

    /**
     * @param int $advertId
     */
    public function delete(int $advertId): void
    {
        $advert = $this->advertRepository->getById($advertId);
        $this->em->remove($advert);
        $this->em->flush();
    }
}
