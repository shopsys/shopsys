<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class AdvertFacade
{
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
    public function getById($advertId)
    {
        return $this->advertRepository->getById($advertId);
    }

    /**
     * @param string $positionName
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPositionOnCurrentDomain($positionName, $category = null)
    {
        $this->advertPositionRegistry->assertPositionNameIsKnown($positionName);

        return $this->advertRepository->findRandomAdvertByPosition($positionName, $this->domain->getId(), $category);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function create(AdvertData $advertData)
    {
        $advert = $this->advertFactory->create($advertData);

        $this->em->persist($advert);
        $this->em->flush();
        $this->imageFacade->manageImages($advert, $advertData->image);
        $this->em->flush();

        return $advert;
    }

    /**
     * @param int $advertId
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function edit($advertId, AdvertData $advertData)
    {
        $advert = $this->advertRepository->getById($advertId);
        $advert->edit($advertData);

        $this->em->flush();
        $this->imageFacade->manageImages($advert, $advertData->image);
        $this->em->flush();

        return $advert;
    }

    /**
     * @param int $advertId
     */
    public function delete($advertId)
    {
        $advert = $this->advertRepository->getById($advertId);
        $this->em->remove($advert);
        $this->em->flush();
    }
}
