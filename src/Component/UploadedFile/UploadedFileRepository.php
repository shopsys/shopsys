<?php

declare(strict_types=1);

namespace App\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository as BaseUploadedFileRepository;

class UploadedFileRepository extends BaseUploadedFileRepository
{
    /**
     * @param string $entityName
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getAllUploadedFilesByEntityName(string $entityName): array
    {
        return $this->getUploadedFileRepository()->createQueryBuilder('uf', 'uf.entityId')
            ->where('uf.entityName = :entityName')
            ->setParameter(':entityName', $entityName)
            ->getQuery()->execute();
    }
}
