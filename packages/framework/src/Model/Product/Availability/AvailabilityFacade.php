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

    public function getById(int $availabilityId): \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        return $this->availabilityRepository->getById($availabilityId);
    }

    public function create(AvailabilityData $availabilityData): \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        $availability = $this->availabilityFactory->create($availabilityData);
        $this->em->persist($availability);
        $this->em->flush();

        return $availability;
    }

    public function edit(int $availabilityId, AvailabilityData $availabilityData): \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        $availability = $this->availabilityRepository->getById($availabilityId);
        $availability->edit($availabilityData);
        $this->em->flush();

        return $availability;
    }

    public function deleteById(int $availabilityId, ?int $newAvailabilityId = null): void
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

    public function getDefaultInStockAvailability(): \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
    {
        $availabilityId = $this->setting->get(Setting::DEFAULT_AVAILABILITY_IN_STOCK);

        return $this->getById($availabilityId);
    }

    public function setDefaultInStockAvailability(Availability $availability): void
    {
        $this->setting->set(Setting::DEFAULT_AVAILABILITY_IN_STOCK, $availability->getId());
        $this->productAvailabilityRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAll(): array
    {
        return $this->availabilityRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAllExceptId(int $availabilityId): array
    {
        return $this->availabilityRepository->getAllExceptId($availabilityId);
    }

    public function isAvailabilityUsed(Availability $availability): bool
    {
        return $this->availabilityRepository->isAvailabilityUsed($availability);
    }

    public function isAvailabilityDefault(Availability $availability): bool
    {
        return $this->getDefaultInStockAvailability() === $availability;
    }
}
