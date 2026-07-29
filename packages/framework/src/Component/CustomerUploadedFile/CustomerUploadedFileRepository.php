<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Override;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;

class CustomerUploadedFileRepository implements UploadedFileRepositoryInterface
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getCustomerUploadedFileRepository(): EntityRepository
    {
        return $this->em->getRepository(CustomerUploadedFile::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    public function getAllCustomerUploadedFilesByEntity(string $entityName, int $entityId): array
    {
        return $this->getCustomerUploadedFileRepository()->findBy(
            [
                'entityName' => $entityName,
                'entityId' => $entityId,
            ],
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    #[Override]
    public function getUploadedFilesByEntity(string $entityName, int $entityId, string $type): array
    {
        return $this->getCustomerUploadedFileRepository()->findBy(
            [
                'entityName' => $entityName,
                'entityId' => $entityId,
                'type' => $type,
            ],
            [
                'position' => 'asc',
                'id' => 'asc',
            ],
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    #[Override]
    public function getById(int $uploadedFileId): UploadedFileInterface
    {
        $customerUploadedFile = $this->getCustomerUploadedFileRepository()->find($uploadedFileId);

        if ($customerUploadedFile === null) {
            $message = 'CustomerUploadedFile with ID ' . $uploadedFileId . ' does not exist.';

            throw new CustomerFileNotFoundException($message);
        }

        return $customerUploadedFile;
    }

    public function getByIdSlugAndExtensionAndHash(
        int $customerUploadedFileId,
        string $customerUploadedFileSlug,
        string $customerUploadedFileExtension,
        string $hash,
    ): CustomerUploadedFile {
        $queryBuilder = $this->getCustomerUploadedFileRepository()
            ->createQueryBuilder('cuf')
            ->andWhere('cuf.id = :uploadedFileId')->setParameter('uploadedFileId', $customerUploadedFileId)
            ->andWhere('cuf.slug = :uploadedFileSlug')->setParameter('uploadedFileSlug', $customerUploadedFileSlug)
            ->andWhere('cuf.extension = :uploadedFileExtension')->setParameter('uploadedFileExtension', $customerUploadedFileExtension)
            ->andWhere('cuf.hash = :hash')->setParameter('hash', $hash);

        $customerUploadedFile = $queryBuilder->getQuery()->getOneOrNullResult();

        $this->checkExists($customerUploadedFile, $customerUploadedFileId, $customerUploadedFileSlug, $customerUploadedFileExtension);

        return $customerUploadedFile;
    }

    public function getNewCustomerUploadedFilePosition(
        string $entityName,
        int $entityId,
        string $type = 'default',
    ): int {
        $queryBuilder = $this->getCustomerUploadedFileRepository()
            ->createQueryBuilder('cuf', 'cuf.id')
            ->select('MAX(cuf.position)')
            ->andWhere('cuf.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('cuf.entityId = :entityId')->setParameter('entityId', $entityId)
            ->andWhere('cuf.type = :type')->setParameter('type', $type);

        $position = $queryBuilder->getQuery()->getSingleScalarResult();

        return $position === null ? 0 : $position + 1;
    }

    protected function checkExists(
        ?object $customerUploadedFile,
        int $customerUploadedFileId,
        string $customerUploadedFileSlug,
        string $customerUploadedFileExtension,
    ): void {
        if ($customerUploadedFile === null) {
            throw new CustomerFileNotFoundException(
                sprintf(
                    'UploadedFile with ID "%s", slug "%s" and extension "%s" does not exist.',
                    $customerUploadedFileId,
                    $customerUploadedFileSlug,
                    $customerUploadedFileExtension,
                ),
            );
        }
    }
}
