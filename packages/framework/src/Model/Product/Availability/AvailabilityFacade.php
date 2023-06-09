<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AvailabilityFacade
{
    protected EntityManagerInterface $em;

    protected AvailabilityRepository $availabilityRepository;

    protected Setting $setting;

    protected ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler;

    protected AvailabilityFactoryInterface $availabilityFactory;

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityRepository $availabilityRepository
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFactoryInterface $availabilityFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $em,
        AvailabilityRepository $availabilityRepository,
        Setting $setting,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        AvailabilityFactoryInterface $availabilityFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->availabilityRepository = $availabilityRepository;
        $this->setting = $setting;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->availabilityFactory = $availabilityFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $availabilityId
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function getById($availabilityId)
    {
        return $this->availabilityRepository->getById($availabilityId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function create(AvailabilityData $availabilityData)
    {
        $availability = $this->availabilityFactory->create($availabilityData);
        $this->em->persist($availability);
        $this->em->flush();

        $this->dispatchAvailabilityEvent($availability, AvailabilityEvent::CREATE);

        return $availability;
    }

    /**
     * @param int $availabilityId
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $availabilityData
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function edit($availabilityId, AvailabilityData $availabilityData)
    {
        $availability = $this->availabilityRepository->getById($availabilityId);
        $availability->edit($availabilityData);
        $this->em->flush();

        $this->dispatchAvailabilityEvent($availability, AvailabilityEvent::UPDATE);

        return $availability;
    }

    /**
     * @param int $availabilityId
     * @param int|null $newAvailabilityId
     */
    public function deleteById($availabilityId, $newAvailabilityId = null)
    {
        $availability = $this->availabilityRepository->getById($availabilityId);

        if ($newAvailabilityId !== null) {
            $newAvailability = $this->availabilityRepository->getById($newAvailabilityId);

            $this->availabilityRepository->replaceAvailability($availability, $newAvailability);
            if ($this->isAvailabilityDefault($availability)) {
                $this->setDefaultInStockAvailability($newAvailability);
            }
        }

        $this->em->remove($availability);

        $this->dispatchAvailabilityEvent($availability, AvailabilityEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function getDefaultInStockAvailability()
    {
        $availabilityId = $this->setting->get(Setting::DEFAULT_AVAILABILITY_IN_STOCK);

        return $this->getById($availabilityId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     */
    public function setDefaultInStockAvailability(Availability $availability)
    {
        $this->setting->set(Setting::DEFAULT_AVAILABILITY_IN_STOCK, $availability->getId());
        $this->productAvailabilityRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAll()
    {
        return $this->availabilityRepository->getAll();
    }

    /**
     * @param int $availabilityId
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAllExceptId($availabilityId)
    {
        return $this->availabilityRepository->getAllExceptId($availabilityId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return bool
     */
    public function isAvailabilityUsed(Availability $availability)
    {
        return $this->availabilityRepository->isAvailabilityUsed($availability);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return bool
     */
    public function isAvailabilityDefault(Availability $availability)
    {
        return $this->getDefaultInStockAvailability() === $availability;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityEvent class
     */
    protected function dispatchAvailabilityEvent(Availability $availability, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new AvailabilityEvent($availability), $eventType);
    }
}
