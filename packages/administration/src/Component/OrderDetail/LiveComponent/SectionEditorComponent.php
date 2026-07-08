<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection;
use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSectionRegistry;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: self::COMPONENT_NAME,
    template: '@ShopsysAdministration/content/order/detail/components/section_editor.html.twig',
)]
#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class SectionEditorComponent
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'OrderDetail:SectionEditor';

    public const string SECTION_EDITOR_CLOSE_EVENT = 'closeOrderDetailSectionEditorEvent';
    public const string SECTION_EDITOR_OPEN_EVENT = 'openOrderDetailSectionEditorEvent';

    public const string SECTION_EDITOR_OPEN_BROWSER_EVENT = 'order-detail-section-editor:open';
    public const string SECTION_EDITOR_CLOSE_BROWSER_EVENT = 'order-detail-section-editor:close';

    #[LiveProp]
    public int $orderId;

    #[LiveProp]
    public ?string $sectionId = null;

    #[LiveProp]
    public bool $open = false;

    public function __construct(
        protected readonly OrderDetailSectionRegistry $orderDetailSectionRegistry,
    ) {
    }

    #[LiveListener(self::SECTION_EDITOR_OPEN_EVENT)]
    #[CanEdit]
    public function openSectionEditor(#[LiveArg] string $section): void
    {
        $this->sectionId = $section;
        $this->open = true;
        $this->dispatchBrowserEvent(self::SECTION_EDITOR_OPEN_BROWSER_EVENT);
    }

    #[LiveListener(self::SECTION_EDITOR_CLOSE_EVENT)]
    #[LiveAction]
    #[CanEdit]
    public function close(): void
    {
        $this->open = false;
        $this->sectionId = null;
        $this->dispatchBrowserEvent(self::SECTION_EDITOR_CLOSE_BROWSER_EVENT);
    }

    public function getSection(): ?OrderDetailSection
    {
        if ($this->sectionId === null) {
            return null;
        }

        return $this->orderDetailSectionRegistry->getSection($this->sectionId);
    }
}
