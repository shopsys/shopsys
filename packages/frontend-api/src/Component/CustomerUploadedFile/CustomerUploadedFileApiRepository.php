<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUploadedFileApiRepository
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
     * @param string|null $type
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[][]
     */
    public function getAllCustomerUploadedFilesIndexedByEntityId(
        array $entityIds,
        string $entityName,
        ?string $type,
    ): array {
        $customerUploadedFilesByEntityId = array_fill_keys($entityIds, []);
        $queryBuilder = $this->entityManager->getRepository(CustomerUploadedFile::class)
            ->createQueryBuilder('cuf')
            ->andWhere('cuf.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('cuf.entityId IN (:entities)')->setParameter('entities', $entityIds)
            ->addOrderBy('cuf.position', 'asc')
            ->addOrderBy('cuf.id', 'asc');

        if ($type === null) {
            $queryBuilder->andWhere('cuf.type IS NULL');
        } else {
            $queryBuilder->andWhere('cuf.type = :type')->setParameter('type', $type);
        }

        /** @var \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile $customerUploadedFile */
        foreach ($queryBuilder->getQuery()->execute() as $customerUploadedFile) {
            $customerUploadedFilesByEntityId[$customerUploadedFile->getEntityId()][] = $customerUploadedFile;
        }

        return $customerUploadedFilesByEntityId;
    }
}
