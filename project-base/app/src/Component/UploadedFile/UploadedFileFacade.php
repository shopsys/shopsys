<?php

declare(strict_types=1);

namespace App\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade as BaseUploadedFileFacade;

/**
 * @property \App\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository
 * @method __construct(\League\Flysystem\FilesystemOperator $filesystem, \Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper, \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig, \App\Component\UploadedFile\UploadedFileRepository $uploadedFileRepository, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileLocator $uploadedFileLocator, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFactory $uploadedFileFactory, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationFactory $uploadedFileRelationFactory, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRelationRepository $uploadedFileRelationRepository, \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload)
 * @method void assignFilesToProducts(\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[] $uploadedFiles, \App\Model\Product\Product[] $products, string $type = \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig::DEFAULT_TYPE_NAME)
 */
class UploadedFileFacade extends BaseUploadedFileFacade
{
}
