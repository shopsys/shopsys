<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FilesIdsToFilesTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     */
    public function __construct(protected readonly UploadedFileFacade $uploadedFileFacade)
    {
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
                } catch (FileNotFoundException $e) {
                    throw new TransformationFailedException('File not found', 0, $e);
                }
            }
        }

        return $files;
    }
}
