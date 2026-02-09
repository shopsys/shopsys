<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use Override;

final class Action extends AbstractRoutableAction
{
    #[Override]
    public static function create(string $name, string $label, ?string $icon = null): static
    {
        return new self($name, $label, $icon);
    }

    #[Override]
    protected function getTemplate(): string
    {
        return '@ShopsysAdministration/crud/inline/action.html.twig';
    }

    #[Override]
    protected function getTemplateParameters(): array
    {
        $this->prepareRoutableAttributes();

        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'actionRoute' => $this->actionRoute,
        ];
    }
}
