<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;

class UploadedFileDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param string $dataFixturesFilesDirectory
     * @param \League\Flysystem\MountManager $mountManager
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileConfig $uploadedFileConfig
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly string $dataFixturesFilesDirectory,
        private readonly MountManager $mountManager,
        private readonly UploadedFileFacade $uploadedFileFacade,
        private readonly UploadedFileDataFactory $uploadedFileDataFactory,
        private readonly UploadedFileConfig $uploadedFileConfig,
        private readonly FileUpload $fileUpload,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->addUploadedFile(
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1),
            'example-file.pdf',
            $this->createUploadedFileTranslatedNames('Example file'),
        );

        $this->addUploadedFile(
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2),
            'example-file.pdf',
            [$this->domain->getLocale() => 'Example file'],
        );

        $this->addReferencedUploadedFiles($this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2), [1]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }

    /**
     * @param object $entity
     * @param string[] $filenames
     * @param array<int, array<string, string>> $translatedNames
     * @param string $type
     */
    private function addUploadedFiles(
        object $entity,
        array $filenames,
        array $translatedNames,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        $config = $this->uploadedFileConfig->getUploadedFileEntityConfig($entity);
        $temporaryFilenames = [];

        foreach ($filenames as $filename) {
            $path = implode(DIRECTORY_SEPARATOR, [
                $this->dataFixturesFilesDirectory,
                $config->getEntityName(),
                $filename,
            ]);

            $temporaryFilename = $this->fileUpload->getTemporaryFilename($filename);
            $temporaryFilenames[] = $temporaryFilename;

            $this->mountManager->copy(
                'local://' . $path,
                'main://' . $this->fileUpload->getTemporaryFilepath($temporaryFilename),
            );
        }

        $uploadedFileData = $this->uploadedFileDataFactory->createByEntity($entity);
        $uploadedFileData->uploadedFiles = $temporaryFilenames;
        $uploadedFileData->uploadedFilenames = $filenames;
        $uploadedFileData->names = $translatedNames;
        $this->uploadedFileFacade->manageFiles($entity, $uploadedFileData, $type);
    }

    /**
     * @param object $entity
     * @param string $filename
     * @param array<string, string> $translatedNames
     * @param string $type
     */
    private function addUploadedFile(
        object $entity,
        string $filename,
        array $translatedNames,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        $this->addUploadedFiles($entity, [$filename], [$translatedNames], $type);
    }

    /**
     * @param object $entity
     * @param int[] $uploadedFileIds
     * @param string $type
     */
    private function addReferencedUploadedFiles(
        object $entity,
        array $uploadedFileIds,
        string $type = UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
    ): void {
        $uploadedFileData = $this->uploadedFileDataFactory->createByEntity($entity);
        $uploadedFileData->relations = $this->uploadedFileFacade->getByIds($uploadedFileIds);
        $this->uploadedFileFacade->manageFiles($entity, $uploadedFileData, $type);
    }

    /**
     * @param string $name
     * @return array<string, string>
     */
    private function createUploadedFileTranslatedNames(string $name): array
    {
        $names = [];

        foreach ($this->domain->getAllLocales() as $locale) {
            $names[$locale] = sprintf('%s - %s', $name, $locale);
        }

        return $names;
    }
}
