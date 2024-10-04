<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class ParameterRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param string[] $parameterUuids
     * @return array<string, int>
     */
    public function getParameterIdsIndexedByUuids(array $parameterUuids): array
    {
        return $this->getIdsIndexedByUuids($parameterUuids, Parameter::class);
    }

    /**
     * @param string[] $parameterValueUuids
     * @return array<string, int>
     */
    public function getParameterValueIdsIndexedByUuids(array $parameterValueUuids): array
    {
        return $this->getIdsIndexedByUuids($parameterValueUuids, ParameterValue::class);
    }

    /**
     * @param string $text
     * @param string $locale
     * @return int
     */
    public function getParameterValueIdByText(string $text, string $locale): int
    {
        return $this->entityManager->createQueryBuilder()
            ->select('pv.id')
            ->from(ParameterValue::class, 'pv')
            ->where('pv.text = :text')
            ->andWhere('pv.locale = :locale')
            ->setParameters([
                'text' => $text,
                'locale' => $locale,
            ])
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string[] $uuids
     * @param string $entityName
     * @return array<string, int>
     */
    private function getIdsIndexedByUuids(array $uuids, string $entityName): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('p.id, p.uuid')
            ->from($entityName, 'p')
            ->where('p.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids);

        if ($entityName === Parameter::class) {
            $queryBuilder->orderBy('p.orderingPriority', 'DESC');
        }

        $idsIndexedByUuids = [];

        foreach ($queryBuilder->getQuery()->getArrayResult() as $idAndUuid) {
            $idsIndexedByUuids[$idAndUuid['uuid']] = $idAndUuid['id'];
        }

        return $idsIndexedByUuids;
    }
}
