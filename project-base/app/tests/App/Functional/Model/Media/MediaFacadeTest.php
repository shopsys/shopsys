<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Media;

use App\DataFixtures\Demo\MediaDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Media\Exception\MediaNotFoundException;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaDataFactory;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\App\Test\TransactionFunctionalTestCase;

class MediaFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private MediaFacade $mediaFacade;

    /**
     * @inject
     */
    private MediaDataFactory $mediaDataFactory;

    /**
     * @inject
     */
    private FileUpload $fileUpload;

    public function testCreateFromTemporaryFileStoresDimensionsAndImage(): void
    {
        $media = $this->createMedia('Uploaded image', 40, 30);

        $this->assertSame('Uploaded image', $media->getTitle());
        $this->assertSame(40, $media->getWidth());
        $this->assertSame(30, $media->getHeight());

        $domainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);
        $imageUrl = $this->mediaFacade->getImageUrl($domainConfig, $media);

        $this->assertStringContainsString('/images/media/', $imageUrl);
        $this->assertStringEndsNotWith('?width=240', $imageUrl);
        $this->assertStringEndsWith('?width=240', $this->mediaFacade->getImageUrl($domainConfig, $media, 240));
    }

    public function testEditWithoutNewImageKeepsDimensions(): void
    {
        $media = $this->createMedia('Uploaded image', 40, 30);

        $mediaData = $this->mediaDataFactory->createFromMedia($media);
        $mediaData->title = 'Renamed image';
        $this->mediaFacade->edit($media, $mediaData);

        $this->assertSame('Renamed image', $media->getTitle());
        $this->assertSame(40, $media->getWidth());
        $this->assertSame(30, $media->getHeight());
    }

    public function testEditWithNewImageUpdatesDimensions(): void
    {
        $media = $this->createMedia('Uploaded image', 40, 30);

        $mediaData = $this->mediaDataFactory->createFromMedia($media);
        $mediaData->image->uploadedFiles[] = $this->uploadImage(80, 20);
        $this->mediaFacade->edit($media, $mediaData);

        $this->assertSame(80, $media->getWidth());
        $this->assertSame(20, $media->getHeight());
    }

    public function testDelete(): void
    {
        $media = $this->createMedia('Uploaded image', 40, 30);
        $mediaId = $media->getId();

        $this->mediaFacade->delete($media);

        $this->expectException(MediaNotFoundException::class);
        $this->mediaFacade->getById($mediaId);
    }

    public function testGetPaginatedByTitle(): void
    {
        $this->createMedia('Žluťoučký kůň', 40, 30);

        $result = $this->mediaFacade->getPaginatedByTitle('zlutoucky', 1, 12);

        $this->assertSame(1, $result->getTotalCount());
        $this->assertSame('Žluťoučký kůň', $result->getResults()[0]->getTitle());

        $demoResult = $this->mediaFacade->getPaginatedByTitle('Demo image', 1, 4);

        $this->assertSame(MediaDataFixture::MEDIA_COUNT, $demoResult->getTotalCount());
        $this->assertCount(4, $demoResult->getResults());
        $this->assertSame(2, $demoResult->getPageCount());
    }

    private function createMedia(string $title, int $width, int $height): Media
    {
        $mediaData = $this->mediaDataFactory->create();
        $mediaData->title = $title;
        $mediaData->image->uploadedFiles[] = $this->uploadImage($width, $height);

        return $this->mediaFacade->create($mediaData);
    }

    private function uploadImage(int $width, int $height): string
    {
        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'media_test_');
        $gdImage = imagecreatetruecolor($width, $height);
        imagepng($gdImage, $temporaryFilePath);

        return $this->fileUpload->upload(new UploadedFile($temporaryFilePath, 'image.png'));
    }
}
