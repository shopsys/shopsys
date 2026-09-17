<?php

declare(strict_types=1);

namespace Tests\App\Functional\Form;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Administrator\Administrator;
use App\Model\Product\Brand\Brand;
use App\Model\Product\Brand\BrandDataFactory;
use App\Model\Product\Product;
use League\Flysystem\MountManager;
use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Form\ImageUploadType;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\TransactionFunctionalTestCase;
use Twig\Environment;

/**
 * Covers saving the image upload form from a browser tab that was loaded before the images were changed from another tab
 */
final class ImageUploadTypeTest extends TransactionFunctionalTestCase
{
    private const string TEST_IMAGE_PATH = __DIR__ . '/../Component/Image/Resources/image.jpg';

    /**
     * Demo product without any image
     */
    private const string PRODUCT_WITHOUT_IMAGES_REFERENCE = ProductDataFixture::PRODUCT_PREFIX . '23';

    /**
     * @inject
     */
    private FormFactoryInterface $formFactory;

    /**
     * @inject
     */
    private ImageFacade $imageFacade;

    /**
     * @inject
     */
    private ImageUploadDataFactory $imageUploadDataFactory;

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
    private BrandFacade $brandFacade;

    /**
     * @inject
     */
    private BrandDataFactory $brandDataFactory;

    /**
     * @inject
     */
    private Environment $twig;

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

        $this->product = $this->getReference(self::PRODUCT_WITHOUT_IMAGES_REFERENCE, Product::class);
        $this->assertSame([], $this->getStoredImageIds($this->product), 'The test expects a product without images');
    }

    public function testStaleFormWithoutImagesKeepsImagesUploadedMeanwhile(): void
    {
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $imageIdsUploadedMeanwhile = $this->uploadImages($this->product, 2);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame($imageIdsUploadedMeanwhile, $this->getStoredImageIds($this->product));
    }

    public function testStaleFormUploadIsAppendedAfterImagesUploadedMeanwhile(): void
    {
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $imageIdsUploadedMeanwhile = $this->uploadImages($this->product, 2);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit, 1);

        $storedImageIds = $this->getStoredImageIds($this->product);
        $this->assertCount(3, $storedImageIds);
        $this->assertSame($imageIdsUploadedMeanwhile, array_slice($storedImageIds, 0, 2));
    }

    public function testStaleFormDoesNotRestoreImageDeletedMeanwhile(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $this->deleteImages($this->product, [$firstImageId]);
        [$thirdImageId] = $this->uploadImages($this->product, 1);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondImageId, $thirdImageId], $this->getStoredImageIds($this->product));
    }

    public function testStaleFormIsValidAfterAllImagesWereDeletedMeanwhile(): void
    {
        $imageIds = $this->uploadImages($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $this->deleteImages($this->product, $imageIds);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([], $this->getStoredImageIds($this->product));
    }

    public function testBothTabsDeleteDifferentImages(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $initialDataToSubmit['imagesToDelete'] = [(string)$secondImageId];
        $this->deleteImages($this->product, [$firstImageId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([], $this->getStoredImageIds($this->product));
    }

    public function testBothTabsDeleteTheSameImage(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $initialDataToSubmit['imagesToDelete'] = [(string)$firstImageId];
        $this->deleteImages($this->product, [$firstImageId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondImageId], $this->getStoredImageIds($this->product));
    }

    public function testStaleFormOrderOverwritesOrderChangedMeanwhile(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $this->reorderImages($this->product, [$secondImageId, $firstImageId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$firstImageId, $secondImageId], $this->getStoredImageIds($this->product));
    }

    public function testStaleFormReorderKeepsImageUploadedMeanwhileLast(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);

        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $initialDataToSubmit['orderedImages'] = [(string)$secondImageId, (string)$firstImageId];
        [$thirdImageId] = $this->uploadImages($this->product, 1);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondImageId, $firstImageId, $thirdImageId], $this->getStoredImageIds($this->product));
    }

    public function testStaleFormRenamesOnlyImagesStillStored(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $locale = $this->getFirstDomainLocale();
        $initialDataToSubmit['namesIndexedByImageIdAndLocale'][$firstImageId][$locale] = 'renamed first';
        $initialDataToSubmit['namesIndexedByImageIdAndLocale'][$secondImageId][$locale] = 'renamed second';
        $this->deleteImages($this->product, [$firstImageId]);

        $this->submitStaleFormAndSave($this->product, Product::class, $initialDataToSubmit);

        $this->assertSame([$secondImageId], $this->getStoredImageIds($this->product));
        $this->assertSame('renamed second', $this->imageFacade->getById($secondImageId)->getName($locale));
    }

    public function testStaleFormIsRenderedAfterUnrelatedValidationError(): void
    {
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($this->product);
        $this->uploadImages($this->product, 2);
        $initialDataToSubmit['uploadedFiles'] = [$this->copyTestImageToTemporaryDirectory()];
        $initialDataToSubmit['uploadedFilenames'] = [[$this->getFirstDomainLocale() => str_repeat('x', 246)]];

        $form = $this->createForm($this->product, Product::class);
        $form->submit($initialDataToSubmit);
        $html = $this->twig->createTemplate('{{ form_row(form) }}')->render(['form' => $form->createView()]);

        $this->assertFalse($form->isValid(), 'The too long file name has to make the form invalid');
        $this->assertStringNotContainsString('js-file-upload-file"', $html, 'Cards of images unknown to the stale form must not be rendered');
    }

    public function testMultipleImagesAreManagedInOneTab(): void
    {
        [$firstImageId, $secondImageId] = $this->uploadImages($this->product, 2);
        $submittedData = $this->createSubmittedDataFromStoredImages($this->product);
        $locale = $this->getFirstDomainLocale();
        $submittedData['orderedImages'] = [(string)$secondImageId, (string)$firstImageId];
        $submittedData['imagesToDelete'] = [(string)$firstImageId];
        $submittedData['namesIndexedByImageIdAndLocale'][$secondImageId][$locale] = 'renamed second';

        $this->submitStaleFormAndSave($this->product, Product::class, $submittedData, 1);

        $storedImageIds = $this->getStoredImageIds($this->product);
        $this->assertCount(2, $storedImageIds);
        $this->assertSame($secondImageId, $storedImageIds[0], 'The reordered image is first, the uploaded one last');
        $this->assertNotContains($firstImageId, $storedImageIds);
        $this->assertSame('renamed second', $this->imageFacade->getById($secondImageId)->getName($locale));
    }

    public function testSingleImageStaleFormWithoutImageKeepsImageUploadedMeanwhile(): void
    {
        $brand = $this->createBrand();
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($brand);
        [$imageIdUploadedMeanwhile] = $this->uploadImages($brand, 1);

        $this->submitStaleFormAndSave($brand, Brand::class, $initialDataToSubmit);

        $this->assertSame([$imageIdUploadedMeanwhile], $this->getStoredImageIds($brand));
    }

    public function testSingleImageStaleFormUploadReplacesImageUploadedMeanwhile(): void
    {
        $brand = $this->createBrand();
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($brand);
        [$imageIdUploadedMeanwhile] = $this->uploadImages($brand, 1);

        $this->submitStaleFormAndSave($brand, Brand::class, $initialDataToSubmit, 1);

        $storedImageIds = $this->getStoredImageIds($brand);
        $this->assertCount(1, $storedImageIds);
        $this->assertNotContains($imageIdUploadedMeanwhile, $storedImageIds);
    }

    public function testSingleImageStaleFormKeepsReplacementUploadedMeanwhile(): void
    {
        $brand = $this->createBrand();
        $this->uploadImages($brand, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($brand);
        [$replacementImageId] = $this->uploadImages($brand, 1);

        $this->submitStaleFormAndSave($brand, Brand::class, $initialDataToSubmit);

        $this->assertSame([$replacementImageId], $this->getStoredImageIds($brand));
    }

    public function testSingleImageStaleFormUploadReplacesReplacementUploadedMeanwhile(): void
    {
        $brand = $this->createBrand();
        $this->uploadImages($brand, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($brand);
        [$replacementImageId] = $this->uploadImages($brand, 1);

        $this->submitStaleFormAndSave($brand, Brand::class, $initialDataToSubmit, 1);

        $storedImageIds = $this->getStoredImageIds($brand);
        $this->assertCount(1, $storedImageIds);
        $this->assertNotContains($replacementImageId, $storedImageIds);
    }

    public function testSingleImageStaleFormIsValidAfterImageWasDeletedMeanwhile(): void
    {
        $brand = $this->createBrand();
        $imageIds = $this->uploadImages($brand, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($brand);
        $this->deleteImages($brand, $imageIds);

        $this->submitStaleFormAndSave($brand, Brand::class, $initialDataToSubmit);

        $this->assertSame([], $this->getStoredImageIds($brand));
    }

    public function testSingleImageStaleFormUploadAfterImageWasDeletedMeanwhile(): void
    {
        $brand = $this->createBrand();
        $imageIds = $this->uploadImages($brand, 1);
        $initialDataToSubmit = $this->createSubmittedDataFromStoredImages($brand);
        $this->deleteImages($brand, $imageIds);

        $this->submitStaleFormAndSave($brand, Brand::class, $initialDataToSubmit, 1);

        $this->assertCount(1, $this->getStoredImageIds($brand));
    }

    public function testSingleImageIsReplacedByUploadInOneTab(): void
    {
        $brand = $this->createBrand();
        [$originalImageId] = $this->uploadImages($brand, 1);
        $submittedData = $this->createSubmittedDataFromStoredImages($brand);

        $this->submitStaleFormAndSave($brand, Brand::class, $submittedData, 1);

        $storedImageIds = $this->getStoredImageIds($brand);
        $this->assertCount(1, $storedImageIds);
        $this->assertNotContains($originalImageId, $storedImageIds);
    }

    /**
     * Snapshot of what the browser would send for the currently stored images
     *
     * @return array<string, mixed>
     */
    private function createSubmittedDataFromStoredImages(object $entity): array
    {
        $images = $this->imageFacade->getImagesByEntityIndexedById($entity, null);

        return [
            'orderedImages' => array_map(static fn (int $imageId) => (string)$imageId, array_keys($images)),
            'namesIndexedByImageIdAndLocale' => array_map(static fn (Image $image) => $image->getNames(), $images),
            'imagesToDelete' => [],
            'uploadedFiles' => [],
            'uploadedFilenames' => [],
        ];
    }

    /**
     * Submits the snapshot to a form built from the current state, the same way the second request does
     *
     * @param class-string $imageEntityClass
     * @param array<string, mixed> $dataToSubmit
     * @param int $uploadedImagesCount images uploaded in the stale tab before saving
     */
    private function submitStaleFormAndSave(
        object $entity,
        string $imageEntityClass,
        array $dataToSubmit,
        int $uploadedImagesCount = 0,
    ): void {
        $form = $this->createForm($entity, $imageEntityClass);
        $form->submit($dataToSubmit);

        $this->assertTrue($form->isValid(), 'Form is not valid: ' . (string)$form->getErrors(true));

        /** @var \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadData $imageUploadData */
        $imageUploadData = $form->getData();

        for ($i = 0; $i < $uploadedImagesCount; $i++) {
            $imageUploadData->uploadedFiles[] = $this->copyTestImageToTemporaryDirectory();
            $imageUploadData->uploadedFilenames[] = [$this->getFirstDomainLocale() => 'uploaded in stale tab ' . $i];
        }

        $this->imageFacade->manageImages($entity, $imageUploadData);
        $this->em->clear();
    }

    /**
     * @param class-string $imageEntityClass
     */
    private function createForm(object $entity, string $imageEntityClass): FormInterface
    {
        return $this->formFactory->create(
            ImageUploadType::class,
            $this->imageUploadDataFactory->createFromEntityAndType($entity),
            [
                'entity' => $entity,
                'image_entity_class' => $imageEntityClass,
                'csrf_protection' => false,
            ],
        );
    }

    /**
     * @return int[] ids of the uploaded images in their stored order
     */
    private function uploadImages(object $entity, int $count): array
    {
        $imageIdsBefore = $this->getStoredImageIds($entity);
        $imageUploadData = $this->imageUploadDataFactory->createFromEntityAndType($entity);

        for ($i = 0; $i < $count; $i++) {
            $imageUploadData->uploadedFiles[] = $this->copyTestImageToTemporaryDirectory();
            $imageUploadData->uploadedFilenames[] = [$this->getFirstDomainLocale() => 'uploaded image ' . $i];
        }

        $this->imageFacade->manageImages($entity, $imageUploadData);
        $this->em->clear();

        return array_values(array_diff($this->getStoredImageIds($entity), $imageIdsBefore));
    }

    /**
     * @param int[] $imageIds
     */
    private function deleteImages(object $entity, array $imageIds): void
    {
        $imageUploadData = $this->imageUploadDataFactory->createFromEntityAndType($entity);
        $imageUploadData->imagesToDelete = array_map(fn (int $imageId) => $this->imageFacade->getById($imageId), $imageIds);

        $this->imageFacade->manageImages($entity, $imageUploadData);
        $this->em->clear();
    }

    /**
     * @param int[] $orderedImageIds
     */
    private function reorderImages(object $entity, array $orderedImageIds): void
    {
        $imageUploadData = $this->imageUploadDataFactory->createFromEntityAndType($entity);
        $imageUploadData->orderedImages = array_map(fn (int $imageId) => $this->imageFacade->getById($imageId), $orderedImageIds);

        $this->imageFacade->manageImages($entity, $imageUploadData);
        $this->em->clear();
    }

    /**
     * @return int[] ids ordered by the stored position
     */
    private function getStoredImageIds(object $entity): array
    {
        return array_keys($this->imageFacade->getImagesByEntityIndexedById($entity, null));
    }

    private function copyTestImageToTemporaryDirectory(): string
    {
        $temporaryFilename = $this->fileUpload->getTemporaryFilename(basename(self::TEST_IMAGE_PATH));
        $this->mountManager->copy(
            'local://' . self::TEST_IMAGE_PATH,
            'main://' . $this->fileUpload->getTemporaryFilepath($temporaryFilename),
        );

        return $temporaryFilename;
    }

    /**
     * Brand allows a single image only
     */
    private function createBrand(): Brand
    {
        $brandData = $this->brandDataFactory->create();
        $brandData->name = 'Brand for stale image form test';

        /** @var \App\Model\Product\Brand\Brand $brand */
        $brand = $this->brandFacade->create($brandData);

        return $brand;
    }
}
