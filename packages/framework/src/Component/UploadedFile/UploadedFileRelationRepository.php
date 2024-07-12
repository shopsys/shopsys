<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class UploadedFileRelationRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     * @return int[]
     */
    public function getUploadedFileIdsByEntityNameIdAndNameAndUploadedFiles(
        string $entityName,
        int $entityId,
        array $uploadedFiles,
    ): array {
        $result = $this->createQueryBuilderByEntityNameIdAndNameAndUploadedFiles($entityName, $entityId, $uploadedFiles)
            ->select('IDENTITY(ur.uploadedFile) as uploadedFileId')
            ->getQuery()
            ->getResult();

        return array_column($result, 'uploadedFileId');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getUploadedFileRelationRepository(): EntityRepository
    {
        return $this->em->getRepository(UploadedFileRelation::class);
    }

    /**
     * @param string $entityName
     * @param int $getEntityId
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation[]
     */
    public function getRelationsForUploadedFiles(string $entityName, int $getEntityId, array $uploadedFiles)
    {
        return $this->createQueryBuilderByEntityNameIdAndNameAndUploadedFiles($entityName, $getEntityId, $uploadedFiles)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param array $uploadedFiles
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilderByEntityNameIdAndNameAndUploadedFiles(
        string $entityName,
        int $entityId,
        array $uploadedFiles,
    ): QueryBuilder {
        return $this->getUploadedFileRelationRepository()
            ->createQueryBuilder('ur')
            ->where('ur.entityName = :entityName')
            ->andWhere('ur.entityId = :entityId')
            ->andWhere('ur.uploadedFile IN (:uploadedFiles)')
            ->setParameter('entityName', $entityName)
            ->setParameter('entityId', $entityId)
            ->setParameter('uploadedFiles', $uploadedFiles);
    }
}
