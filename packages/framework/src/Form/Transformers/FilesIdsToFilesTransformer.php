<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\Form\DataTransformerInterface;

class FilesIdsToFilesTransformer implements DataTransformerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade
     */
    private $uploadedFileFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(UploadedFileFacade $uploadedFileFacade)
    {
        $this->uploadedFileFacade = $uploadedFileFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $files
     * @return int[]
     */
    public function transform($files): array
    {
        $fileIds = [];

        if (is_iterable($files)) {
            foreach ($files as $file) {
                $fileIds[] = $file->getId();
            }
        }

        return $fileIds;
    }

    /**
     * @param int[] $fileIds
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function reverseTransform($fileIds): array
    {
        $files = [];

        if (is_array($fileIds)) {
            foreach ($fileIds as $fileId) {
                try {
                    $files[] = $this->uploadedFileFacade->getById((int)$fileId);
                } catch (\Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException $e) {
                    throw new \Symfony\Component\Form\Exception\TransformationFailedException('File not found', null, $e);
                }
            }
        }

        return $files;
    }
}
