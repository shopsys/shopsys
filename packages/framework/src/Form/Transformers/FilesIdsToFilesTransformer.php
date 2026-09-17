<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Transformers;

use Override;
use Shopsys\FrameworkBundle\Component\UploadedFile\Exception\FileNotFoundException;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\Form\DataTransformerInterface;

class FilesIdsToFilesTransformer implements DataTransformerInterface
{
    public function __construct(protected readonly UploadedFileFacade $uploadedFileFacade)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $files
     * @return int[]
     */
    #[Override]
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
    #[Override]
    public function reverseTransform($fileIds): array
    {
        $files = [];

        if (is_array($fileIds)) {
            foreach ($fileIds as $fileId) {
                // the file is not in the submitted form, e.g. it was uploaded from another browser tab after the form was loaded
                if ($fileId === null || (string)$fileId === '') {
                    continue;
                }

                try {
                    $files[] = $this->uploadedFileFacade->getById((int)$fileId);
                } catch (FileNotFoundException) {
                    // the file was deleted from another browser tab after the form was loaded
                    continue;
                }
            }
        }

        return $files;
    }
}
