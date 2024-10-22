<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

final class CrudConfigData
{
    public array $customPageTitles = [
        PageType::CREATE->value => null,
        PageType::EDIT->value => null,
        PageType::LIST->value => null,
        PageType::DETAIL->value => null
    ];

    public array $defaultActions = [PageType::LIST];

    public function getTitle(PageType $pageType): ?string
    {
        return $this->customPageTitles[$pageType->value] ?? null;
    }

    /**
     * @return PageType[]
     */
    public function getDefaultActions(): array
    {
        return $this->defaultActions;
    }
}