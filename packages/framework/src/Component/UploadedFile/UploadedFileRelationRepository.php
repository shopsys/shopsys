<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class UploadedFileRelationRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getUploadedFileRelationRepository(): EntityRepository
    {
        return $this->em->getRepository(UploadedFileRelation::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation[]
     */
    public function getByEntityNameAndIdAndUploadedFiles(
        string $entityName,
        int $entityId,
        array $uploadedFiles,
        string $type,
    ): array {
        return $this->createQueryBuilderByEntityNameIdAndNameAndUploadedFiles(
            $entityName,
            [$entityId],
            $uploadedFiles,
            $type,
        )
            ->getQuery()
            ->getResult();
    }

    /**
     * @return int[]
     */
    public function getEntityIdsForUploadedFile(UploadedFile $uploadedFile, string $entityName, string $type): array
    {
        $result = $this->createQueryBuilderByUploadedFiles([$uploadedFile])
            ->andWhere('ur.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('ur.type = :type')->setParameter('type', $type)
            ->select('ur.entityId')
            ->getQuery()
            ->getResult();

        return array_column($result, 'entityId');
    }

    /**
     * @param int[] $entityIds
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     */
    public function deleteRelationsByEntityNameAndIdsAndUploadedFiles(
        string $entityName,
        array $entityIds,
        array $uploadedFiles,
        string $type,
    ): void {
        $this->createQueryBuilderByEntityNameIdAndNameAndUploadedFiles($entityName, $entityIds, $uploadedFiles, $type)
            ->delete(UploadedFileRelation::class, 'ur')
            ->getQuery()
            ->execute();
    }

    /**
     * @param int[] $entityIds
     * @return array<int, int>
     */
    public function maxPositionsByEntityNameAndIds(string $entityName, array $entityIds, string $type): array
    {
        $qb = $this->getUploadedFileRelationRepository()->createQueryBuilder('ur');

        $result = $this->extendQueryBuilderByEntityNameAndIds($qb, $entityName, $entityIds, $type)
            ->select('ur.entityId, MAX(ur.position) as maxPosition')
            ->groupBy('ur.entityId')
            ->getQuery()
            ->getResult();

        $indexed = [];

        foreach ($result as $row) {
            $indexed[$row['entityId']] = $row['maxPosition'];
        }

        return $indexed;
    }

    /**
     * @param int[] $entityIds
     */
    protected function createQueryBuilderByEntityNameIdAndNameAndUploadedFiles(
        string $entityName,
        array $entityIds,
        array $uploadedFiles,
        string $type,
    ): QueryBuilder {
        return $this->extendQueryBuilderByEntityNameAndIds(
            $this->createQueryBuilderByUploadedFiles($uploadedFiles),
            $entityName,
            $entityIds,
            $type,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles
     */
    protected function createQueryBuilderByUploadedFiles(array $uploadedFiles): QueryBuilder
    {
        return $this->getUploadedFileRelationRepository()
            ->createQueryBuilder('ur')
            ->andWhere('ur.uploadedFile IN (:uploadedFiles)')
            ->setParameter('uploadedFiles', $uploadedFiles);
    }

    protected function extendQueryBuilderByEntityNameAndIds(
        QueryBuilder $qb,
        string $entityName,
        array $entityIds,
        string $type,
    ): QueryBuilder {
        return $qb->andWhere('ur.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('ur.entityId IN (:entityIds)')->setParameter('entityIds', $entityIds)
            ->andWhere('ur.type = :type')->setParameter('type', $type);
    }
}
