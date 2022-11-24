<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UnitFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitRepository
     */
    protected $unitRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFactoryInterface
     */
    protected $unitFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitRepository $unitRepository
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitFactoryInterface $unitFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManagerInterface $em,
        UnitRepository $unitRepository,
        Setting $setting,
        UnitFactoryInterface $unitFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->unitRepository = $unitRepository;
        $this->setting = $setting;
        $this->unitFactory = $unitFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getById(int $unitId): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        return $this->unitRepository->getById($unitId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function create(UnitData $unitData): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        $unit = $this->unitFactory->create($unitData);
        $this->em->persist($unit);
        $this->em->flush();

        $this->dispatchUnitEvent($unit, UnitEvent::CREATE);

        return $unit;
    }

    /**
     * @param int $unitId
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\UnitData $unitData
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function edit(int $unitId, UnitData $unitData): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        $unit = $this->unitRepository->getById($unitId);
        $unit->edit($unitData);
        $this->em->flush();

        $this->dispatchUnitEvent($unit, UnitEvent::UPDATE);

        return $unit;
    }

    /**
     * @param int $unitId
     * @param int|null $newUnitId
     */
    public function deleteById(int $unitId, ?int $newUnitId = null): void
    {
        $oldUnit = $this->unitRepository->getById($unitId);

        // intentionally called before unit ids in product are changed
        $this->dispatchUnitEvent($oldUnit, UnitEvent::DELETE);

        if ($newUnitId !== null) {
            $newUnit = $this->unitRepository->getById($newUnitId);
            $this->unitRepository->replaceUnit($oldUnit, $newUnit);
            if ($this->isUnitDefault($oldUnit)) {
                $this->setDefaultUnit($newUnit);
            }
        }

        $this->em->remove($oldUnit);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getAll(): array
    {
        return $this->unitRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     * @return bool
     */
    public function isUnitUsed(Unit $unit): bool
    {
        return $this->unitRepository->existsProductWithUnit($unit);
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getAllExceptId(int $unitId): array
    {
        return $this->unitRepository->getAllExceptId($unitId);
    }

    /**
     * @return int
     */
    protected function getDefaultUnitId(): int
    {
        return $this->setting->get(Setting::DEFAULT_UNIT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getDefaultUnit(): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        $defaultUnitId = $this->getDefaultUnitId();

        return $this->unitRepository->getById($defaultUnitId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     */
    public function setDefaultUnit(Unit $unit): void
    {
        $this->setting->set(Setting::DEFAULT_UNIT, $unit->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     * @return bool
     */
    public function isUnitDefault(Unit $unit): bool
    {
        return $this->getDefaultUnit() === $unit;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Unit\UnitEvent class
     */
    protected function dispatchUnitEvent(Unit $unit, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new UnitEvent($unit), $eventType);
    }
}
