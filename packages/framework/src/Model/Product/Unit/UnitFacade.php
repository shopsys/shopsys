<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UnitFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly UnitRepository $unitRepository,
        protected readonly Setting $setting,
        protected readonly UnitFactory $unitFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getById(int $unitId): Unit
    {
        return $this->unitRepository->getById($unitId);
    }

    public function create(UnitData $unitData): Unit
    {
        $unit = $this->unitFactory->create($unitData);
        $this->em->persist($unit);
        $this->em->flush();

        $this->dispatchUnitEvent($unit, UnitEvent::CREATE);

        return $unit;
    }

    public function edit(int $unitId, UnitData $unitData): Unit
    {
        $unit = $this->unitRepository->getById($unitId);
        $unit->edit($unitData);
        $this->em->flush();

        $this->dispatchUnitEvent($unit, UnitEvent::UPDATE);

        return $unit;
    }

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

    public function isUnitUsed(Unit $unit): bool
    {
        return $this->unitRepository->existsProductWithUnit($unit) || $this->unitRepository->existsParameterWithUnit($unit);
    }

    public function isAtLeastOneUnitCreated(): bool
    {
        return $this->unitRepository->isAtLeastOneUnitCreated();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getAllExceptId(int $unitId): array
    {
        return $this->unitRepository->getAllExceptId($unitId);
    }

    protected function getDefaultUnitId(): int
    {
        return $this->setting->get(Setting::DEFAULT_UNIT);
    }

    public function getDefaultUnit(): Unit
    {
        $defaultUnitId = $this->getDefaultUnitId();

        return $this->unitRepository->getById($defaultUnitId);
    }

    public function setDefaultUnit(Unit $unit): void
    {
        $this->setting->set(Setting::DEFAULT_UNIT, $unit->getId());
    }

    public function isUnitDefault(Unit $unit): bool
    {
        return $this->getDefaultUnit() === $unit;
    }

    /**
     * @see \Shopsys\FrameworkBundle\Model\Product\Unit\UnitEvent class
     */
    protected function dispatchUnitEvent(Unit $unit, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new UnitEvent($unit), $eventType);
    }
}
