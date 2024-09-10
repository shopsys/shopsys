<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CustomerUploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileInterface;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUploadedFileRepository implements UploadedFileRepositoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerUploadedFileRepository(): EntityRepository
    {
        return $this->em->getRepository(CustomerUploadedFile::class);
    }

    /**
     * @param string $entityName
     * @param int $entityId
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
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
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
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    public function getById(int $uploadedFileId): UploadedFileInterface
    {
        $customerUploadedFile = $this->getCustomerUploadedFileRepository()->find($uploadedFileId);

        if ($customerUploadedFile === null) {
            $message = 'CustomerUploadedFile with ID ' . $uploadedFileId . ' does not exist.';

            throw new CustomerFileNotFoundException($message);
        }

        return $customerUploadedFile;
    }

    /**
     * @param int $customerUploadedFileId
     * @param string $customerUploadedFileSlug
     * @param string $customerUploadedFileExtension
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $hash
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile
     */
    public function getByIdSlugAndExtension(
        int $customerUploadedFileId,
        string $customerUploadedFileSlug,
        string $customerUploadedFileExtension,
        ?CustomerUser $customerUser = null,
        ?string $hash = null,
    ): CustomerUploadedFile {
        $queryBuilder = $this->getCustomerUploadedFileRepository()
            ->createQueryBuilder('cuf')
            ->andWhere('cuf.id = :uploadedFileId')->setParameter('uploadedFileId', $customerUploadedFileId)
            ->andWhere('cuf.slug = :uploadedFileSlug')->setParameter('uploadedFileSlug', $customerUploadedFileSlug)
            ->andWhere('cuf.extension = :uploadedFileExtension')->setParameter('uploadedFileExtension', $customerUploadedFileExtension);

        if ($customerUser) {
            $queryBuilder->andWhere('cuf.customerUser = :customerUser')
                ->setParameter('customerUser', $customerUser);
        }

        if ($hash) {
            $queryBuilder->andWhere('cuf.hash = :hash')
                ->setParameter('hash', $hash);
        }

        $customerUploadedFile = $queryBuilder->getQuery()->getOneOrNullResult();

        $this->checkExists($customerUploadedFile, $customerUploadedFileId, $customerUploadedFileSlug, $customerUploadedFileExtension);

        return $customerUploadedFile;
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @return int
     */
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

        return $position === null ? 0 : ++$position;
    }

    /**
     * @param object|null $customerUploadedFile
     * @param int $customerUploadedFileId
     * @param string $customerUploadedFileSlug
     * @param string $customerUploadedFileExtension
     */
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
