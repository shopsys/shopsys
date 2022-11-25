<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class AdvertFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Advert\AdvertRepository
     */
    protected $advertRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Advert\AdvertFactoryInterface
     */
    protected $advertFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry
     */
    protected $advertPositionRegistry;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertRepository $advertRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertFactoryInterface $advertFactory
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry $advertPositionRegistry
     */
    public function __construct(
        EntityManagerInterface $em,
        AdvertRepository $advertRepository,
        ImageFacade $imageFacade,
        Domain $domain,
        AdvertFactoryInterface $advertFactory,
        AdvertPositionRegistry $advertPositionRegistry
    ) {
        $this->em = $em;
        $this->advertRepository = $advertRepository;
        $this->imageFacade = $imageFacade;
        $this->domain = $domain;
        $this->advertFactory = $advertFactory;
        $this->advertPositionRegistry = $advertPositionRegistry;
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
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert|null
     */
    public function findRandomAdvertByPositionOnCurrentDomain(string $positionName): ?Advert
    {
        $this->advertPositionRegistry->assertPositionNameIsKnown($positionName);

        return $this->advertRepository->findRandomAdvertByPosition($positionName, $this->domain->getId());
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
        $this->imageFacade->manageImages($advert, $advertData->image);
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
        $this->imageFacade->manageImages($advert, $advertData->image);
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
