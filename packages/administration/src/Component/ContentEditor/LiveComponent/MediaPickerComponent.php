<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\LiveComponent;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaDataFactory;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: self::COMPONENT_NAME,
    template: '@ShopsysAdministration/content_editor/media_picker.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_MEDIA)]
final class MediaPickerComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'Admin:ContentEditor:MediaPicker';

    public const string OPEN_EVENT = 'content-editor:media-picker:open';

    public const string OPENED_BROWSER_EVENT = 'content-editor:media-picker:opened';
    public const string CLOSED_BROWSER_EVENT = 'content-editor:media-picker:closed';
    public const string MEDIA_SELECTED_BROWSER_EVENT = 'content-editor:media-selected';

    private const int PAGE_SIZE = 12;

    #[LiveProp(writable: true, onUpdated: 'resetPage')]
    public string $search = '';

    #[LiveProp]
    public int $page = 1;

    #[LiveProp]
    public bool $open = false;

    #[LiveProp]
    public ?string $targetInputId = null;

    /**
     * Distinguishes the pickers of several editors on one page, so one does not open the modal of another
     */
    #[LiveProp]
    public string $instanceId = '';

    private ?PaginationResult $paginationResult = null;

    public function __construct(
        private readonly MediaFacade $mediaFacade,
        private readonly MediaDataFactory $mediaDataFactory,
        private readonly Domain $domain,
    ) {
    }

    #[LiveListener(self::OPEN_EVENT)]
    #[CanView]
    public function openPicker(#[LiveArg] string $targetInputId): void
    {
        $this->targetInputId = $targetInputId;
        $this->open = true;
        $this->dispatchBrowserEvent($this->getOpenedBrowserEvent());
    }

    #[LiveAction]
    #[CanView]
    public function close(): void
    {
        $this->open = false;
        $this->dispatchBrowserEvent($this->getClosedBrowserEvent());
    }

    #[LiveAction]
    #[CanView]
    public function goToPage(#[LiveArg] int $page): void
    {
        $this->page = $page;
    }

    #[LiveAction]
    #[CanView]
    public function select(#[LiveArg] int $mediaId): void
    {
        $this->selectMedia($this->mediaFacade->getById($mediaId));
    }

    #[LiveAction]
    #[CanCreate]
    public function createFromTemporaryFile(#[LiveArg] string $temporaryFilename, #[LiveArg] string $title): void
    {
        $mediaData = $this->mediaDataFactory->create();
        $mediaData->title = $title;
        $mediaData->image->uploadedFiles[] = $temporaryFilename;

        $this->selectMedia($this->mediaFacade->create($mediaData));
    }

    public function resetPage(): void
    {
        $this->page = 1;
    }

    public function getOpenedBrowserEvent(): string
    {
        return self::OPENED_BROWSER_EVENT . ':' . $this->instanceId;
    }

    public function getClosedBrowserEvent(): string
    {
        return self::CLOSED_BROWSER_EVENT . ':' . $this->instanceId;
    }

    public function getPaginationResult(): PaginationResult
    {
        $this->paginationResult ??= $this->mediaFacade->getPaginatedByTitle($this->search, $this->page, self::PAGE_SIZE);

        return $this->paginationResult;
    }

    private function selectMedia(Media $media): void
    {
        $this->open = false;
        $this->dispatchBrowserEvent($this->getClosedBrowserEvent());
        $this->dispatchBrowserEvent(self::MEDIA_SELECTED_BROWSER_EVENT, [
            'targetInputId' => $this->targetInputId,
            'mediaId' => $media->getId(),
            'url' => $this->mediaFacade->getImageUrl($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID), $media),
            'title' => $media->getTitle(),
        ]);
    }
}
