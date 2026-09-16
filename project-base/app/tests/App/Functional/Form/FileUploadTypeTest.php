<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Administrator\Administrator;
use App\Model\Product\Parameter\ParameterRepository;
use App\Model\Product\Product;
use League\Flysystem\MountManager;
use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Form\FileUploadType;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;

/**
 * Covers saving the file upload form from a browser tab that was loaded before the files were changed from another tab
 */
final class FileUploadTypeTest extends TransactionFunctionalTestCase
{
    private const string TEST_FILE_PATH = __DIR__ . '/../../../../src/DataFixtures/resources/uploaded_files/product/example-file.pdf';

    /**
     * Demo product without any file
     */
    private const string PRODUCT_WITHOUT_FILES_REFERENCE = ProductDataFixture::PRODUCT_PREFIX . '23';

    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    /**
     * @inject
     */
    private UploadedFileFacade $uploadedFileFacade;

    /**
     * @inject
     */
    private UploadedFileDataFactory $uploadedFileDataFactory;

    /**
     * @inject
     */
    private FileUpload $fileUpload;

    /**
     * @inject
     */
    private MountManager $mountManager;

    /**
     * @inject
     */
    private ParameterRepository $parameterRepository;

    /**
     * @inject
     */
    private ParameterValueDataFactory $parameterValueDataFactory;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    private Product $product;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        // the localized fields of the form need a request and the logged administrator to resolve the admin-enabled domains
        $this->createRequest();
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->tokenStorage->setToken(new UsernamePasswordToken($administrator, 'administration', $administrator->getRoles()));

        $this->product = $this->getReference(self::PRODUCT_WITHOUT_FILES_REFERENCE, Product::class);
        $this->assertSame([], $this->getStoredFileIds($this->product), 'The test expects a product without files');
    }

    public function testStaleFormWithoutFilesKeepsFilesUploadedMeanwhile(): void
    {
        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $fileIdsUploadedMeanwhile = $this->uploadFiles($this->product, 2);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame($fileIdsUploadedMeanwhile, $this->getStoredFileIds($this->product));
    }

    public function testStaleFormUploadIsAppendedAfterFilesUploadedMeanwhile(): void
    {
        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $fileIdsUploadedMeanwhile = $this->uploadFiles($this->product, 2);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit, 1);

        $storedFileIds = $this->getStoredFileIds($this->product);
        $this->assertCount(3, $storedFileIds);
        $this->assertSame($fileIdsUploadedMeanwhile, array_slice($storedFileIds, 0, 2));
    }

    public function testStaleFormDoesNotRestoreFileDeletedMeanwhile(): void
    {
        [$firstFileId, $secondFileId] = $this->uploadFiles($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $this->deleteFiles($this->product, [$firstFileId]);
        [$thirdFileId] = $this->uploadFiles($this->product, 1);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondFileId, $thirdFileId], $this->getStoredFileIds($this->product));
    }

    public function testStaleFormIsValidAfterAllFilesWereDeletedMeanwhile(): void
    {
        $fileIds = $this->uploadFiles($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $this->deleteFiles($this->product, $fileIds);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([], $this->getStoredFileIds($this->product));
    }

    public function testBothTabsDeleteDifferentFiles(): void
    {
        [$firstFileId, $secondFileId] = $this->uploadFiles($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $initialDataToSubmit['filesToDelete'] = [(string)$secondFileId];
        $this->deleteFiles($this->product, [$firstFileId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([], $this->getStoredFileIds($this->product));
    }

    public function testBothTabsDeleteTheSameFile(): void
    {
        [$firstFileId, $secondFileId] = $this->uploadFiles($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $initialDataToSubmit['filesToDelete'] = [(string)$firstFileId];
        $this->deleteFiles($this->product, [$firstFileId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondFileId], $this->getStoredFileIds($this->product));
    }

    public function testStaleFormOrderOverwritesOrderChangedMeanwhile(): void
    {
        [$firstFileId, $secondFileId] = $this->uploadFiles($this->product, 2);
        $staleSubmittedData = $this->createSubmittedDataFromStoredFiles($this->product);
        $this->reorderFiles($this->product, [$secondFileId, $firstFileId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $staleSubmittedData);

        $this->assertSame([$firstFileId, $secondFileId], $this->getStoredFileIds($this->product));
    }

    public function testStaleFormReorderKeepsFileUploadedMeanwhileLast(): void
    {
        [$firstFileId, $secondFileId] = $this->uploadFiles($this->product, 2);
        $staleSubmittedData = $this->createSubmittedDataFromStoredFiles($this->product);
        $staleSubmittedData['orderedFiles'] = [(string)$secondFileId, (string)$firstFileId];
        [$thirdFileId] = $this->uploadFiles($this->product, 1);

        $this->submitStaleFormAndSave($this->product, Product::class, $staleSubmittedData);

        $this->assertSame([$secondFileId, $firstFileId, $thirdFileId], $this->getStoredFileIds($this->product));
    }

    public function testStaleFormRenamesOnlyFilesStillStored(): void
    {
        [$firstFileId, $secondFileId] = $this->uploadFiles($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($this->product);
        $locale = $this->getFirstDomainLocale();
        $initialDataToSubmit['currentFilenamesIndexedById'][$firstFileId] = 'renamed-first';
        $initialDataToSubmit['currentFilenamesIndexedById'][$secondFileId] = 'renamed-second';
        $initialDataToSubmit['namesIndexedById'][$firstFileId][$locale] = 'Renamed first';
        $initialDataToSubmit['namesIndexedById'][$secondFileId][$locale] = 'Renamed second';
        $this->deleteFiles($this->product, [$firstFileId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondFileId], $this->getStoredFileIds($this->product));
        $secondFile = $this->uploadedFileFacade->getById($secondFileId);
        $this->assertSame('renamed-second', $secondFile->getName());
        $this->assertSame('Renamed second', $secondFile->getTranslatedName($locale));
    }

    public function testSingleFileStaleFormWithoutFileKeepsFileUploadedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();

        $staleSubmittedData = $this->createSubmittedDataFromStoredFiles($parameterValue);
        [$fileIdUploadedMeanwhile] = $this->uploadFiles($parameterValue, 1);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $staleSubmittedData);

        $this->assertSame([$fileIdUploadedMeanwhile], $this->getStoredFileIds($parameterValue));
    }

    public function testSingleFileStaleFormUploadReplacesFileUploadedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();

        $staleSubmittedData = $this->createSubmittedDataFromStoredFiles($parameterValue);
        [$fileIdUploadedMeanwhile] = $this->uploadFiles($parameterValue, 1);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $staleSubmittedData, 1);

        $storedFileIds = $this->getStoredFileIds($parameterValue);
        $this->assertCount(1, $storedFileIds);
        $this->assertNotContains($fileIdUploadedMeanwhile, $storedFileIds);
    }

    public function testSingleFileStaleFormPickerSelectionReplacesFileUploadedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();

        $staleSubmittedData = $this->createSubmittedDataFromStoredFiles($parameterValue);
        $this->uploadFiles($parameterValue, 1);
        $pickedFileId = $this->getExistingDemoFileId();
        $staleSubmittedData['relations'] = [(string)$pickedFileId];

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $staleSubmittedData);

        $this->assertSame([$pickedFileId], $this->getStoredFileIds($parameterValue));
    }

    public function testSingleFileStaleFormKeepsReplacementUploadedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();
        $this->uploadFiles($parameterValue, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($parameterValue);
        [$replacementFileId] = $this->uploadFiles($parameterValue, 1);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $initialDataToSubmit);

        $this->assertSame([$replacementFileId], $this->getStoredFileIds($parameterValue));
    }

    public function testSingleFileStaleFormUploadReplacesReplacementUploadedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();

        $this->uploadFiles($parameterValue, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($parameterValue);
        [$replacementFileId] = $this->uploadFiles($parameterValue, 1);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $initialDataToSubmit, 1);

        $storedFileIds = $this->getStoredFileIds($parameterValue);
        $this->assertCount(1, $storedFileIds);
        $this->assertNotContains($replacementFileId, $storedFileIds);
    }

    public function testSingleFileStaleFormIsValidAfterFileWasDeletedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();
        $fileIds = $this->uploadFiles($parameterValue, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($parameterValue);
        $this->deleteFiles($parameterValue, $fileIds);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $initialDataToSubmit);

        $this->assertSame([], $this->getStoredFileIds($parameterValue));
    }

    public function testSingleFileStaleFormUploadAfterFileWasDeletedMeanwhile(): void
    {
        $parameterValue = $this->createParameterValue();

        $fileIds = $this->uploadFiles($parameterValue, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredFiles($parameterValue);
        $this->deleteFiles($parameterValue, $fileIds);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $initialDataToSubmit, 1);

        $this->assertCount(1, $this->getStoredFileIds($parameterValue));
    }

    public function testSingleFileIsReplacedByUploadInOneTab(): void
    {
        $parameterValue = $this->createParameterValue();

        [$originalFileId] = $this->uploadFiles($parameterValue, 1);
        $submittedData = $this->createSubmittedDataFromStoredFiles($parameterValue);

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $submittedData, 1);

        $storedFileIds = $this->getStoredFileIds($parameterValue);
        $this->assertCount(1, $storedFileIds);
        $this->assertNotContains($originalFileId, $storedFileIds);
    }

    public function testSingleFileIsReplacedByPickerSelectionInOneTab(): void
    {
        $parameterValue = $this->createParameterValue();

        $this->uploadFiles($parameterValue, 1);
        $submittedData = $this->createSubmittedDataFromStoredFiles($parameterValue);
        $pickedFileId = $this->getExistingDemoFileId();
        $submittedData['relations'] = [(string)$pickedFileId];

        $this->submitStaleFormAndSave($parameterValue, ParameterValue::class, $submittedData);

        $this->assertSame([$pickedFileId], $this->getStoredFileIds($parameterValue));
    }

    /**
     * Snapshot of what the browser would send for the currently stored files
     *
     * @return array<string, mixed>
     */
    private function createSubmittedDataFromStoredFiles(object $entity): array
    {
        $files = $this->getStoredFilesIndexedById($entity);

        $submittedData = [
            'orderedFiles' => array_map(static fn (int $fileId) => (string)$fileId, array_keys($files)),
            'currentFilenamesIndexedById' => array_map(static fn (UploadedFile $file) => $file->getName(), $files),
            'filesToDelete' => [],
            'uploadedFiles' => [],
            'uploadedFilenames' => [],
        ];

        // the friendly name fields exist only for entities that require them (see uploaded_files.yaml)
        if ($entity instanceof Product) {
            $submittedData['namesIndexedById'] = array_map(static fn (UploadedFile $file) => $file->getTranslatedNames(), $files);
        }

        return $submittedData;
    }

    /**
     * Submits the snapshot to a form built from the current state, the same way the second request does
     *
     * @param class-string $fileEntityClass
     * @param array<string, mixed> $submittedData
     * @param int $uploadedFilesCount files uploaded in the stale tab before saving
     */
    private function submitStaleFormAndSave(
        object $entity,
        string $fileEntityClass,
        array $submittedData,
        int $uploadedFilesCount = 0,
    ): void {
        $form = $this->createForm($entity, $fileEntityClass);
        $form->submit($submittedData);

        $this->assertTrue($form->isValid(), 'Form is not valid: ' . (string)$form->getErrors(true));

        /** @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData $uploadedFileData */
        $uploadedFileData = $form->getData();
        $this->addUploads($uploadedFileData, $uploadedFilesCount, 'uploaded in stale tab');

        $this->uploadedFileFacade->manageFiles($entity, $uploadedFileData);
        // the entity facades flush after managing the files, the test does the same
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @param class-string $fileEntityClass
     */
    private function createForm(object $entity, string $fileEntityClass): FormInterface
    {
        return $this->formFactory->create(
            FileUploadType::class,
            $this->uploadedFileDataFactory->createByEntity($entity),
            [
                'entity' => $entity,
                'file_entity_class' => $fileEntityClass,
                'csrf_protection' => false,
            ],
        );
    }

    /**
     * @return int[] ids of the uploaded files in their stored order
     */
    private function uploadFiles(object $entity, int $count): array
    {
        $fileIdsBefore = $this->getStoredFileIds($entity);
        $uploadedFileData = $this->uploadedFileDataFactory->createByEntity($entity);
        $this->addUploads($uploadedFileData, $count, 'uploaded file');

        $this->uploadedFileFacade->manageFiles($entity, $uploadedFileData);
        // the entity facades flush after managing the files, the test does the same
        $this->em->flush();
        $this->em->clear();

        return array_values(array_diff($this->getStoredFileIds($entity), $fileIdsBefore));
    }

    private function addUploads(
        UploadedFileData $uploadedFileData,
        int $count,
        string $namePrefix,
    ): void {
        for ($i = 0; $i < $count; $i++) {
            $uploadedFileData->uploadedFiles[] = $this->copyTestFileToTemporaryDirectory();
            $uploadedFileData->uploadedFilenames[] = basename(self::TEST_FILE_PATH);
            $uploadedFileData->names[] = [$this->getFirstDomainLocale() => $namePrefix . ' ' . $i];
        }
    }

    /**
     * @param int[] $fileIds
     */
    private function deleteFiles(object $entity, array $fileIds): void
    {
        $uploadedFileData = $this->uploadedFileDataFactory->createByEntity($entity);
        $uploadedFileData->filesToDelete = array_map(fn (int $fileId) => $this->uploadedFileFacade->getById($fileId), $fileIds);

        $this->uploadedFileFacade->manageFiles($entity, $uploadedFileData);
        // the entity facades flush after managing the files, the test does the same
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @param int[] $orderedFileIds
     */
    private function reorderFiles(object $entity, array $orderedFileIds): void
    {
        $uploadedFileData = $this->uploadedFileDataFactory->createByEntity($entity);
        $uploadedFileData->orderedFiles = array_map(fn (int $fileId) => $this->uploadedFileFacade->getById($fileId), $orderedFileIds);

        $this->uploadedFileFacade->manageFiles($entity, $uploadedFileData);
        // the entity facades flush after managing the files, the test does the same
        $this->em->flush();
        $this->em->clear();
    }

    /**
     * The demo file of the first product stands in for a file chosen in the file picker
     */
    private function getExistingDemoFileId(): int
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $demoFiles = $this->uploadedFileFacade->getUploadedFilesByEntity($product);

        $this->assertNotEmpty($demoFiles, 'The test expects the first demo product to have a file');

        return array_first($demoFiles)->getId();
    }

    /**
     * @return int[] ids ordered by the stored position
     */
    private function getStoredFileIds(object $entity): array
    {
        return array_keys($this->getStoredFilesIndexedById($entity));
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile>
     */
    private function getStoredFilesIndexedById(object $entity): array
    {
        $filesIndexedById = [];

        foreach ($this->uploadedFileFacade->getUploadedFilesByEntity($entity) as $file) {
            $filesIndexedById[$file->getId()] = $file;
        }

        return $filesIndexedById;
    }

    private function copyTestFileToTemporaryDirectory(): string
    {
        $temporaryFilename = $this->fileUpload->getTemporaryFilename(basename(self::TEST_FILE_PATH));
        $this->mountManager->copy(
            'local://' . self::TEST_FILE_PATH,
            'main://' . $this->fileUpload->getTemporaryFilepath($temporaryFilename),
        );

        return $temporaryFilename;
    }

    /**
     * Parameter value allows a single file only
     */
    private function createParameterValue(): ParameterValue
    {
        $parameterValueData = $this->parameterValueDataFactory->create();
        $parameterValueData->text = 'Parameter value for stale file form test';
        $parameterValueData->locale = $this->getFirstDomainLocale();

        return $this->parameterRepository->findOrCreateParameterValueByParameterValueData($parameterValueData);
    }
}
