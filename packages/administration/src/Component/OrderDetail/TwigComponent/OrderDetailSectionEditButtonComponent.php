<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\TwigComponent;

use Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent\SectionEditorComponent;
use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'OrderDetail:SectionEditButton',
    template: '@ShopsysAdministration/content/order/detail/components/section_edit_button.html.twig',
)]
class OrderDetailSectionEditButtonComponent
{
    public OrderDetailSection $section;

    public ?bool $canEdit = null;

    /**
     * Builds the Live "emit" parameter that opens the section editor for this section,
     * e.g. "name(OrderDetail:SectionEditor)|openOrderDetailSectionEditor".
     */
    public function getOpenEditorEmitParam(): string
    {
        return sprintf(
            'name(%s)|%s',
            SectionEditorComponent::COMPONENT_NAME,
            SectionEditorComponent::SECTION_EDITOR_OPEN_EVENT,
        );
    }
}
