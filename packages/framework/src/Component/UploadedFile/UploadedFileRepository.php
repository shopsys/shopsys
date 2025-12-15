<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Component\AbstractUploadedFile\UploadedFileRepositoryInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;

class UploadedFileRepository implements UploadedFileRepositoryInterface
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
    protected function getUploadedFileRepository(): EntityRepository
    {
        return $this->em->getRepository(UploadedFile::class);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getAllUploadedFilesByEntity(string $entityName, int $entityId): array
    {
        return $this->getUploadedFileRepository()->findBy(
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
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    #[Override]
    public function getUploadedFilesByEntity(string $entityName, int $entityId, string $type): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->from(UploadedFile::class, 'u')
            ->join(UploadedFileRelation::class, 'ur', Join::WITH, 'u = ur.uploadedFile')
            ->select('u')
            ->andWhere('ur.type = :type')
            ->setParameter('type', $type)
            ->andWhere('ur.entityName = :entityName')
            ->setParameter('entityName', $entityName)
            ->andWhere('ur.entityId = :entityId')
            ->setParameter('entityId', $entityId)
            ->addOrderBy('ur.position', 'asc')
            ->addOrderBy('ur.id', 'asc');

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    #[Override]
    public function getById(int $uploadedFileId): UploadedFile
    {
        $uploadedFile = $this->getUploadedFileRepository()->find($uploadedFileId);

        if ($uploadedFile === null) {
            $message = 'UploadedFile with ID ' . $uploadedFileId . ' does not exist.';

            throw new FileNotFoundException($message);
        }

        return $uploadedFile;
    }

    /**
     * @param int $uploadedFileId
     * @param string $uploadedFileSlug
     * @param string $uploadedFileExtension
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getByIdSlugAndExtension(
        int $uploadedFileId,
        string $uploadedFileSlug,
        string $uploadedFileExtension,
    ): UploadedFile {
        $uploadedFile = $this->getUploadedFileRepository()->findOneBy(
            [
                'id' => $uploadedFileId,
                'slug' => $uploadedFileSlug,
                'extension' => $uploadedFileExtension,
            ],
        );

        if ($uploadedFile === null) {
            throw new FileNotFoundException(
                sprintf(
                    'UploadedFile with ID "%s", slug "%s" and extension "%s" does not exist.',
                    $uploadedFileId,
                    $uploadedFileSlug,
                    $uploadedFileExtension,
                ),
            );
        }

        return $uploadedFile;
    }

    /**
     * @param int[] $uploadedFileIds
     * @throws \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getByIds(array $uploadedFileIds): array
    {
        $uploadedFiles = $this->getUploadedFileRepository()->findBy(['id' => $uploadedFileIds]);

        if (count($uploadedFileIds) !== count($uploadedFiles)) {
            $foundUploadedFileIds = array_map(fn (UploadedFile $uploadedFile) => $uploadedFile->getId(), $uploadedFiles);

            throw new FileNotFoundException(
                sprintf(
                    'UploadedFiles with IDs %s do not exist.',
                    implode(', ', array_diff($uploadedFileIds, $foundUploadedFileIds)),
                ),
            );
        }

        return $uploadedFiles;
    }

    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileTranslation[]
     */
    public function getAllTranslationsByUploadedFileId(int $uploadedFileId): array
    {
        return $this->em->createQueryBuilder()
            ->select('ut')
            ->from(UploadedFileTranslation::class, 'ut')
            ->andWhere('ut.translatable = :uploadedFileId')->setParameter('uploadedFileId', $uploadedFileId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int[] $entityIds
     * @param string $entityName
     * @param string|null $requiredLocale
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[][]
     */
    public function getAllFilesIndexedByEntityId(
        array $entityIds,
        string $entityName,
        ?string $requiredLocale,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): array {
        $filesByEntityId = array_fill_keys($entityIds, []);
        $queryBuilder = $this->em->getRepository(UploadedFileRelation::class)
            ->createQueryBuilder('ur')
            ->join('ur.uploadedFile', 'u')
            ->addSelect('u')
            ->andWhere('ur.entityName = :entityName')->setParameter('entityName', $entityName)
            ->andWhere('ur.type = :type')->setParameter('type', $type)
            ->andWhere('ur.entityId IN (:entities)')->setParameter('entities', $entityIds)
            ->addOrderBy('ur.position', 'asc')
            ->addOrderBy('u.id', 'asc');

        if ($requiredLocale !== null) {
            $queryBuilder
                ->join('u.translations', 't', 'WITH', 't.locale = :locale AND t.name IS NOT NULL')
                ->setParameter('locale', $requiredLocale);
        }

        /** @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelation $fileRelation */
        foreach ($queryBuilder->getQuery()->execute() as $fileRelation) {
            $filesByEntityId[$fileRelation->getEntityId()][] = $fileRelation->getUploadedFile();
        }

        return $filesByEntityId;
    }
}
