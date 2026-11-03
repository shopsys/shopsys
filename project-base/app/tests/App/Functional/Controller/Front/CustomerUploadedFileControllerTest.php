<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Front;

use App\DataFixtures\Demo\ComplaintDataFixture;
use Override;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\ApplicationTestCase;

final class CustomerUploadedFileControllerTest extends ApplicationTestCase
{
    private const string HTML_FILE_NAME = 'evil.html';

    private const string HTML_FILE_NAME_WITH_IMAGE_EXTENSION = 'evil.jpg';

    private const string HTML_FILE_CONTENT = '<script>alert(1)</script>';

    /**
     * @inject
     */
    private CustomerUploadedFileFacade $customerUploadedFileFacade;

    /**
     * @inject
     */
    private CustomerUploadedFileDataFactory $customerUploadedFileDataFactory;

    /**
     * @inject
     */
    private FileUpload $fileUpload;

    private ?CustomerUploadedFile $uploadedHtmlFile = null;

    #[Override]
    protected function tearDown(): void
    {
        if ($this->uploadedHtmlFile !== null) {
            $this->customerUploadedFileFacade->deleteFileFromFilesystem($this->uploadedHtmlFile);
        }

        parent::tearDown();
    }

    public function testViewableExtensionIsServedInlineWithMimeTypeFromExtensionRegardlessOfContent(): void
    {
        $this->uploadedHtmlFile = $this->uploadHtmlFileToComplaintItem(self::HTML_FILE_NAME_WITH_IMAGE_EXTENSION);

        $response = $this->requestViewUrl($this->uploadedHtmlFile);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringStartsWith('image/jpeg', (string)$response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline', (string)$response->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function testNotViewableFileIsServedAsAttachment(): void
    {
        $this->uploadedHtmlFile = $this->uploadHtmlFileToComplaintItem(self::HTML_FILE_NAME);

        $response = $this->requestViewUrl($this->uploadedHtmlFile);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertStringContainsString('attachment', (string)$response->headers->get('Content-Disposition'));
        $this->assertStringNotContainsString('inline', (string)$response->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    private function requestViewUrl(CustomerUploadedFile $customerUploadedFile): Response
    {
        $this->configureCurrentClient(null, null);
        self::getCurrentClient()->request('GET', $this->getViewUrl($customerUploadedFile));

        return self::getCurrentClient()->getResponse();
    }

    private function getViewUrl(CustomerUploadedFile $customerUploadedFile): string
    {
        return $this->customerUploadedFileFacade->getCustomerUploadedFileViewUrl(
            $this->domain->getCurrentDomainConfig(),
            $customerUploadedFile,
        );
    }

    private function getComplaintItem(): ComplaintItem
    {
        $complaint = $this->getReference(ComplaintDataFixture::COMPLAINT_PREFIX . '1', Complaint::class);
        $complaintItems = $complaint->getItems();

        return array_first($complaintItems);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFile[]
     */
    private function getComplaintItemFiles(): array
    {
        return $this->customerUploadedFileFacade->getUploadedFilesByEntity($this->getComplaintItem());
    }

    private function uploadHtmlFileToComplaintItem(string $filename): CustomerUploadedFile
    {
        $complaintItem = $this->getComplaintItem();
        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'customer_uploaded_file_test_');
        file_put_contents($temporaryFilePath, self::HTML_FILE_CONTENT);
        $temporaryFilename = $this->fileUpload->upload(
            new UploadedFile($temporaryFilePath, $filename, 'text/html', null, true),
        );

        $customerUploadedFileData = $this->customerUploadedFileDataFactory->createByEntity($complaintItem);
        $customerUploadedFileData->uploadedFiles[] = $temporaryFilename;
        $customerUploadedFileData->uploadedFilenames[] = $filename;
        $this->customerUploadedFileFacade->manageFiles($complaintItem, $customerUploadedFileData);
        $expectedSlug = pathinfo($filename, PATHINFO_FILENAME);
        $expectedExtension = pathinfo($filename, PATHINFO_EXTENSION);

        $complaintItemFiles = $this->getComplaintItemFiles();

        foreach ($complaintItemFiles as $customerUploadedFile) {
            if ($customerUploadedFile->getSlug() === $expectedSlug && $customerUploadedFile->getExtension() === $expectedExtension) {
                return $customerUploadedFile;
            }
        }

        $this->fail(sprintf('The file "%s" was not attached to the complaint item.', $filename));
    }
}
