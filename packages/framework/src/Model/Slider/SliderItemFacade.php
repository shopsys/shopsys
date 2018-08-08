<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class SliderItemFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Slider\SliderItemRepository
     */
    protected $sliderItemRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Slider\SliderItemFactoryInterface
     */
    protected $sliderItemFactory;

    public function __construct(
        EntityManagerInterface $em,
        SliderItemRepository $sliderItemRepository,
        ImageFacade $imageFacade,
        Domain $domain,
        SliderItemFactoryInterface $sliderItemFactory
    ) {
        $this->em = $em;
        $this->sliderItemRepository = $sliderItemRepository;
        $this->imageFacade = $imageFacade;
        $this->domain = $domain;
        $this->sliderItemFactory = $sliderItemFactory;
    }
    
    public function getById(int $sliderItemId): \Shopsys\FrameworkBundle\Model\Slider\SliderItem
    {
        return $this->sliderItemRepository->getById($sliderItemId);
    }

    public function create(SliderItemData $sliderItemData): \Shopsys\FrameworkBundle\Model\Slider\SliderItem
    {
        $sliderItem = $this->sliderItemFactory->create($sliderItemData);

        $this->em->persist($sliderItem);
        $this->em->flush();
        $this->imageFacade->uploadImage($sliderItem, $sliderItemData->image->uploadedFiles, null);

        return $sliderItem;
    }
    
    public function edit(int $sliderItemId, SliderItemData $sliderItemData): \Shopsys\FrameworkBundle\Model\Slider\SliderItem
    {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);
        $sliderItem->edit($sliderItemData);

        $this->em->flush();
        $this->imageFacade->uploadImage($sliderItem, $sliderItemData->image->uploadedFiles, null);

        return $sliderItem;
    }
    
    public function delete(int $sliderItemId): void
    {
        $sliderItem = $this->sliderItemRepository->getById($sliderItemId);

        $this->em->remove($sliderItem);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleOnCurrentDomain(): array
    {
        return $this->sliderItemRepository->getAllVisibleByDomainId($this->domain->getId());
    }
}
