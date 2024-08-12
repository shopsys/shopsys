<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Files;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation;

class FileApiRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string $locale
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[][]
     */
    public function getAllFilesIndexedByEntityId(
        array $entityIds,
        string $entityName,
        string $locale,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        $filesByEntityId = array_fill_keys($entityIds, []);
        $queryBuilder = $this->entityManager->getRepository(UploadedFileRelation::class)
            ->createQueryBuilder('ur')
            ->join('ur.uploadedFile', 'u')
            ->addSelect('u')
            ->andWhere('ur.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('ur.type = :type')->setParameter('type', $type)
            ->andWhere('ur.entityId IN (:entities)')->setParameter('entities', $entityIds)
            ->join('u.translations', 't', 'WITH', 't.locale = :locale AND t.name IS NOT NULL')
            ->setParameter('locale', $locale)
            ->addOrderBy('ur.position', 'asc')
            ->addOrderBy('u.id', 'asc');


        /** @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation $fileRelation */
        foreach ($queryBuilder->getQuery()->execute() as $fileRelation) {
            $filesByEntityId[$fileRelation->getEntityId()][] = $fileRelation->getUploadedFile();
        }

        return $filesByEntityId;
    }
}
