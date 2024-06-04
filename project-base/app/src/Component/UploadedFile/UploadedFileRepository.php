<?php

declare(strict_types=1);

namespace App\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository as BaseUploadedFileRepository;

class UploadedFileRepository extends BaseUploadedFileRepository
{
    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @return int
     */
    public function getUploadedFilesCountByEntityIndexedById(
        string $entityName,
        int $entityId,
        string $type = 'default',
    ): int {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('COUNT(uf)')
            ->from(UploadedFile::class, 'uf', 'uf.id')
            ->andWhere('uf.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('uf.entityId = :entityId')->setParameter('entityId', $entityId)
            ->andWhere('uf.type = :type')->setParameter('type', $type);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
