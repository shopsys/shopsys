<?php

namespace Shopsys\FrameworkBundle\Model\Product\Unit;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

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

    public function __construct(
        EntityManagerInterface $em,
        UnitRepository $unitRepository,
        Setting $setting,
        UnitFactoryInterface $unitFactory
    ) {
        $this->em = $em;
        $this->unitRepository = $unitRepository;
        $this->setting = $setting;
        $this->unitFactory = $unitFactory;
    }
    
    public function getById(int $unitId): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        return $this->unitRepository->getById($unitId);
    }

    public function create(UnitData $unitData): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        $unit = $this->unitFactory->create($unitData);
        $this->em->persist($unit);
        $this->em->flush();

        return $unit;
    }
    
    public function edit(int $unitId, UnitData $unitData): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
    {
        $unit = $this->unitRepository->getById($unitId);
        $unit->edit($unitData);
        $this->em->flush();

        return $unit;
    }

    /**
     * @param int|null $newUnitId
     */
    public function deleteById(int $unitId, ?int $newUnitId = null): void
    {
        $oldUnit = $this->unitRepository->getById($unitId);

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
        return $this->unitRepository->existsProductWithUnit($unit);
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

    public function getDefaultUnit(): \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
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
}
