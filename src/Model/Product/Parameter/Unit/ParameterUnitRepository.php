<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

use App\Model\Product\Parameter\Unit\Exception\UnitNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ParameterUnitRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

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
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ParameterUnit::class);
    }

    /**
     * @param int $id
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit
     */
    public function getById(int $id): ParameterUnit
    {
        $unit = $this->getRepository()->find($id);
        if ($unit === null) {
            throw new UnitNotFoundException();
        }

        return $unit;
    }

    /**
     * @param string $unit
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit|null
     */
    public function findByUnit(string $unit): ?ParameterUnit
    {
        return $this->getRepository()->findOneBy(['unit' => $unit]);
    }
}
