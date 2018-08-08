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

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getById($unitId)
    {
        return $this->unitRepository->getById($unitId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function create(UnitData $unitData)
    {
        $unit = $this->unitFactory->create($unitData);
        $this->em->persist($unit);
        $this->em->flush();

        return $unit;
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function edit($unitId, UnitData $unitData)
    {
        $unit = $this->unitRepository->getById($unitId);
        $unit->edit($unitData);
        $this->em->flush();

        return $unit;
    }

    /**
     * @param int $unitId
     * @param int|null $newUnitId
     */
    public function deleteById($unitId, $newUnitId = null)
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
    public function getAll()
    {
        return $this->unitRepository->getAll();
    }

    /**
     * @return bool
     */
    public function isUnitUsed(Unit $unit)
    {
        return $this->unitRepository->existsProductWithUnit($unit);
    }

    /**
     * @param int $unitId
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getAllExceptId($unitId)
    {
        return $this->unitRepository->getAllExceptId($unitId);
    }

    protected function getDefaultUnitId()
    {
        return $this->setting->get(Setting::DEFAULT_UNIT);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit
     */
    public function getDefaultUnit()
    {
        $defaultUnitId = $this->getDefaultUnitId();

        return $this->unitRepository->getById($defaultUnitId);
    }

    public function setDefaultUnit(Unit $unit)
    {
        $this->setting->set(Setting::DEFAULT_UNIT, $unit->getId());
    }

    /**
     * @return bool
     */
    public function isUnitDefault(Unit $unit)
    {
        return $this->getDefaultUnit() === $unit;
    }
}
