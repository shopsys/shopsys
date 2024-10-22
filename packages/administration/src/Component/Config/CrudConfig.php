<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Webmozart\Assert\Assert;

final class CrudConfig
{
    private CrudConfigData $crudConfigData;

    public function __construct()
    {
        $this->crudConfigData = new CrudConfigData();
    }

    /**
     * Sets a custom title for a given page type.
     *
     * @param PageType $pageType
     * @param string $title
     * @return $this
     */
    public function setTitle(PageType $pageType, string $title): self
    {
        $this->crudConfigData->customPageTitles[$pageType->value] = $title;
        return $this;
    }


    /**
     * Sets which default actions are enabled for the crud controller.
     *
     * @param PageType[] $actions
     * @return $this
     */
    public function setActions(array $actions): self
    {
        Assert::allIsInstanceOfAny($actions, PageType::cases(), 'The given action is not a valid page type');
        $this->crudConfigData->defaultActions = $actions;
        return $this;
    }

    public function getConfig(): CrudConfigData
    {
        return $this->crudConfigData;
    }
}