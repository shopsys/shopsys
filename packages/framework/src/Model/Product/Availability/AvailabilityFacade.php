<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class AvailabilityFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityRepository
     */
    protected $availabilityRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler
     */
    protected $productAvailabilityRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFactoryInterface
     */
    protected $availabilityFactory;

    public function __construct(
        EntityManagerInterface $em,
        AvailabilityRepository $availabilityRepository,
        Setting $setting,
        ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        AvailabilityFactoryInterface $availabilityFactory
    ) {
        $this->em = $em;
        $this->availabilityRepository = $availabilityRepository;
        $this->setting = $setting;
        $this->productAvailabilityRecalculationScheduler = $productAvailabilityRecalculationScheduler;
        $this->availabilityFactory = $availabilityFactory;
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function create(AvailabilityData $availabilityData)
    {
        $availability = $this->availabilityFactory->create($availabilityData);
        $this->em->persist($availability);
        $this->em->flush();

        return $availability;
    }

    /**
     * @param int $availabilityId
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function edit($availabilityId, AvailabilityData $availabilityData)
    {
        $availability = $this->availabilityRepository->getById($availabilityId);
        $availability->edit($availabilityData);
        $this->em->flush();

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
     * @return bool
     */
    public function isAvailabilityUsed(Availability $availability)
    {
        return $this->availabilityRepository->isAvailabilityUsed($availability);
    }

    /**
     * @return bool
     */
    public function isAvailabilityDefault(Availability $availability)
    {
        return $this->getDefaultInStockAvailability() === $availability;
    }
}
