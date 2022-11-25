<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Model\Slider\Exception\SliderItemNotFoundException;

class SliderItemRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getSliderItemRepository(): EntityRepository
    {
        return $this->em->getRepository(SliderItem::class);
    }

    /**
     * @param int $sliderItemId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function getById(int $sliderItemId): SliderItem
    {
        /** @var \Shopsys\FrameworkBundle\Model\Slider\SliderItem|null $sliderItem */
        $sliderItem = $this->getSliderItemRepository()->find($sliderItemId);
        if ($sliderItem === null) {
            $message = 'Slider item with ID ' . $sliderItemId . ' not found.';
            throw new SliderItemNotFoundException($message);
        }
        return $sliderItem;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem|null
     */
    public function findById(int $id): ?SliderItem
    {
        /** @var \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem */
        $sliderItem = $this->getSliderItemRepository()->find($id);
        return $sliderItem;
    }

    /**
     * @return object[]
     */
    public function getAll(): array
    {
        return $this->getSliderItemRepository()->findAll();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem[]
     */
    public function getAllVisibleByDomainId(int $domainId): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('si')
            ->from(SliderItem::class, 'si')
            ->where('si.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->andWhere('si.hidden = false')
            ->orderBy('si.position')
            ->addOrderBy('si.id');

        return $queryBuilder
            ->getQuery()
            ->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class)
            ->execute();
    }
}
