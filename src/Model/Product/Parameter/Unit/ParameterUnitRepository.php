<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

use App\Model\Product\Parameter\Unit\Exception\UnitNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pu')
            ->from(ParameterUnit::class, 'pu');
    }

    /**
     * @param int $id
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit
     */
    public function getById(int $id): ParameterUnit
    {
        $unit = $this->getRepository()->find($id);
        if ($unit === null) {
            $message = sprintf('Parameter unit with ID = `%s` was not found.', $id);
            throw new UnitNotFoundException($message);
        }

        return $unit;
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\Unit\ParameterUnit|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ParameterUnit
    {
        return $this->getRepository()->findOneBy(['akeneoCode' => $akeneoCode]);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllParameterUnitQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilder();
    }
}
