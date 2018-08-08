<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\EntityManagerInterface;

class SliderItemRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getSliderItemRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(SliderItem::class);
    }

    /**
     * @param int $sliderItemId
     */
    public function getById($sliderItemId): \Shopsys\FrameworkBundle\Model\Slider\SliderItem
    {
        $sliderItem = $this->getSliderItemRepository()->find($sliderItemId);
        if ($sliderItem === null) {
            $message = 'Slider item with ID ' . $sliderItemId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Slider\Exception\SliderItemNotFoundException($message);
        }
        return $sliderItem;
    }

    /**
     * @param int $id
     */
    public function findById($id): ?\Shopsys\FrameworkBundle\Model\Slider\SliderItem
    {
        return $this->getSliderItemRepository()->find($id);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAll(): array
    {
        return $this->getSliderItemRepository()->findAll();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleByDomainId($domainId): array
    {
        return $this->getSliderItemRepository()->findBy([
            'domainId' => $domainId,
            'hidden' => false,
        ]);
    }
}
