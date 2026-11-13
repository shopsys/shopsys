<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Front;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Symfony\Component\HttpFoundation\File\UploadedFile as HttpUploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\ApplicationTestCase;

final class UploadedFileControllerTest extends ApplicationTestCase
{
    private const string HTML_FILE_NAME_WITH_PDF_EXTENSION = 'evil.pdf';

    private const string HTML_FILE_CONTENT = '<script>alert(1)</script>';

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

    private ?UploadedFile $uploadedHtmlFile = null;

    #[Override]
    protected function tearDown(): void
    {
        if ($this->uploadedHtmlFile !== null) {
            $this->uploadedFileFacade->deleteFileFromFilesystem($this->uploadedHtmlFile);
        }

        parent::tearDown();
    }

    public function testViewableExtensionIsServedInlineWithMimeTypeFromExtensionRegardlessOfContent(): void
    {
        $this->uploadedHtmlFile = $this->uploadHtmlFileToProduct(self::HTML_FILE_NAME_WITH_PDF_EXTENSION);

        $this->configureCurrentClient(null, null);
        self::getCurrentClient()->request(
            'GET',
            $this->uploadedFileFacade->getUploadedFileViewUrl($this->domain->getCurrentDomainConfig(), $this->uploadedHtmlFile),
        );
        $response = self::getCurrentClient()->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringStartsWith('application/pdf', (string)$response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string)$response->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    private function uploadHtmlFileToProduct(string $filename): UploadedFile
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'uploaded_file_test_');
        file_put_contents($temporaryFilePath, self::HTML_FILE_CONTENT);
        $temporaryFilename = $this->fileUpload->upload(
            new HttpUploadedFile($temporaryFilePath, $filename, 'text/html', null, true),
        );

        $uploadedFileData = $this->uploadedFileDataFactory->createByEntity($product);
        $existingFileIds = array_map(static fn (UploadedFile $uploadedFile): int => $uploadedFile->getId(), $uploadedFileData->orderedFiles);
        $uploadedFileData->uploadedFiles[] = $temporaryFilename;
        $uploadedFileData->uploadedFilenames[] = $filename;
        $this->uploadedFileFacade->manageFiles($product, $uploadedFileData);
        $productFiles = $this->uploadedFileFacade->getUploadedFilesByEntity($product);

        foreach ($productFiles as $uploadedFile) {
            if (!in_array($uploadedFile->getId(), $existingFileIds, true)) {
                return $uploadedFile;
            }
        }

        $this->fail(sprintf('The file "%s" was not attached to the product.', $filename));
    }
}
