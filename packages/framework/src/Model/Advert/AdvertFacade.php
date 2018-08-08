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

    public function __construct(
        EntityManagerInterface $em,
        AdvertRepository $advertRepository,
        ImageFacade $imageFacade,
        Domain $domain,
        AdvertFactoryInterface $advertFactory
    ) {
        $this->em = $em;
        $this->advertRepository = $advertRepository;
        $this->imageFacade = $imageFacade;
        $this->domain = $domain;
        $this->advertFactory = $advertFactory;
    }

    /**
     * @param int $advertId
     */
    public function getById($advertId): \Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        return $this->advertRepository->getById($advertId);
    }

    /**
     * @param string $positionName
     */
    public function findRandomAdvertByPositionOnCurrentDomain($positionName): ?\Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        return $this->advertRepository->findRandomAdvertByPosition($positionName, $this->domain->getId());
    }

    public function create(AdvertData $advertData): \Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        $advert = $this->advertFactory->create($advertData);

        $this->em->persist($advert);
        $this->em->flush();
        $this->imageFacade->uploadImage($advert, $advertData->image->uploadedFiles, null);
        $this->em->flush();

        return $advert;
    }

    /**
     * @param int $advertId
     */
    public function edit($advertId, AdvertData $advertData): \Shopsys\FrameworkBundle\Model\Advert\Advert
    {
        $advert = $this->advertRepository->getById($advertId);
        $advert->edit($advertData);

        $this->em->flush();
        $this->imageFacade->uploadImage($advert, $advertData->image->uploadedFiles, null);
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
