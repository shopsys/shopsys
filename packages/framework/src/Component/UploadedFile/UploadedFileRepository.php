<?php

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\EntityManagerInterface;

class UploadedFileRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getUploadedFileRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(UploadedFile::class);
    }

    public function findUploadedFileByEntity(string $entityName, int $entityId): ?\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
    {
        return $this->getUploadedFileRepository()->findOneBy([
            'entityName' => $entityName,
            'entityId' => $entityId,
        ]);
    }

    public function getUploadedFileByEntity(string $entityName, int $entityId): \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
    {
        $uploadedFile = $this->findUploadedFileByEntity($entityName, $entityId);
        if ($uploadedFile === null) {
            $message = 'UploadedFile not found for entity "' . $entityName . '" with ID ' . $entityId;
            throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException($message);
        }

        return $uploadedFile;
    }

    public function getById(int $uploadedFileId): \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
    {
        $uploadedFile = $this->getUploadedFileRepository()->find($uploadedFileId);

        if ($uploadedFile === null) {
            $message = 'UploadedFile with ID ' . $uploadedFileId . ' does not exist.';
            throw new \Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException($message);
        }

        return $uploadedFile;
    }
}
