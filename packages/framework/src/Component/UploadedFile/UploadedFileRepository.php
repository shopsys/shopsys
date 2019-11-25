<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class UploadedFileRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
            ]
        );
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function getUploadedFilesByEntity(string $entityName, int $entityId): array
    {
        return $this->getUploadedFileRepository()->findBy([
            'entityName' => $entityName,
            'entityId' => $entityId,
        ]);
    }

    /**
     * @param int $uploadedFileId
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function getById(int $uploadedFileId): UploadedFile
    {
        $uploadedFile = $this->getUploadedFileRepository()->find($uploadedFileId);

        if ($uploadedFile === null) {
            $message = 'UploadedFile with ID ' . $uploadedFileId . ' does not exist.';
            throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException($message);
        }

        return $uploadedFile;
    }
}
