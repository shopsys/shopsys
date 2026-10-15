<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\ContentEditor;

use App\DataFixtures\Demo\MediaDataFixture;
use Shopsys\AdministrationBundle\Component\ContentEditor\LiveComponent\MediaPickerComponent;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaDataFactory;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\UX\LiveComponent\LiveResponder;
use Tests\App\Test\TransactionFunctionalTestCase;

class MediaPickerComponentTest extends TransactionFunctionalTestCase
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

    /**
     * @inject
     */
    private LiveResponder $liveResponder;

    public function testOpenPickerRemembersTargetInputAndDispatchesOpenedEvent(): void
    {
        $component = $this->createComponent();

        $component->openPicker('content_image');

        $this->assertTrue($component->open);
        $this->assertSame('content_image', $component->targetInputId);
        $this->assertSame([], $this->getDispatchedBrowserEventNames());
    }

    public function testSelectDispatchesSelectedEventAndClosesPicker(): void
    {
        $media = $this->getReference(MediaDataFixture::MEDIA_PREFIX . '1', Media::class);
        $component = $this->createComponent();
        $component->openPicker('content_image');
        $this->liveResponder->reset();

        $component->select($media->getId());

        $this->assertFalse($component->open);

        $events = $this->liveResponder->getBrowserEventsToDispatch();
        $this->assertSame(
            [MediaPickerComponent::MEDIA_SELECTED_BROWSER_EVENT],
            $this->getDispatchedBrowserEventNames(),
        );

        $payload = $events[1]['payload'];
        $this->assertSame('content_image', $payload['targetInputId']);
        $this->assertSame($media->getId(), $payload['mediaId']);
        $this->assertSame('Demo image 1', $payload['title']);
        $this->assertStringContainsString('/images/media/', $payload['url']);
    }

    public function testCreateFromTemporaryFileCreatesAndSelectsMedia(): void
    {
        $component = $this->createComponent();
        $component->openPicker('content_image');
        $this->liveResponder->reset();

        $component->createFromTemporaryFile($this->uploadImage(40, 30), 'Uploaded in picker');

        $events = $this->liveResponder->getBrowserEventsToDispatch();
        $payload = $events[1]['payload'];
        $media = $this->mediaFacade->getById($payload['mediaId']);

        $this->assertSame(MediaPickerComponent::MEDIA_SELECTED_BROWSER_EVENT, $events[1]['event']);
        $this->assertSame('Uploaded in picker', $media->getTitle());
        $this->assertSame(40, $media->getWidth());
        $this->assertSame(30, $media->getHeight());
    }

    public function testChangingSearchResetsPageAndFiltersByTitle(): void
    {
        $component = $this->createComponent();
        $component->goToPage(2);

        $component->search = 'Demo image 3';
        $component->resetPage();

        $this->assertSame(1, $component->page);
        $this->assertSame(1, $component->getPaginationResult()->getTotalCount());
    }

    private function createComponent(): MediaPickerComponent
    {
        $component = new MediaPickerComponent($this->mediaFacade, $this->mediaDataFactory, $this->domain);
        $component->setLiveResponder($this->liveResponder);
        $this->liveResponder->reset();

        return $component;
    }

    /**
     * @return string[]
     */
    private function getDispatchedBrowserEventNames(): array
    {
        return array_column($this->liveResponder->getBrowserEventsToDispatch(), 'event');
    }

    private function uploadImage(int $width, int $height): string
    {
        $temporaryFilePath = tempnam(sys_get_temp_dir(), 'media_picker_test_');
        imagepng(imagecreatetruecolor($width, $height), $temporaryFilePath);

        return $this->fileUpload->upload(new UploadedFile($temporaryFilePath, 'image.png'));
    }
}
