<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\DatagridAction;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\GlobalAction;
use Shopsys\AdministrationBundle\Component\Config\PageType;
use Webmozart\Assert\Assert;

class ActionsConfig
{
    /**
     * @var \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder[][]
     */
    private array $actions = [
        PageType::CREATE->value => [],
        PageType::EDIT->value => [],
        PageType::LIST->value => [],
        PageType::DETAIL->value => [],
    ];

    /**
     * @param class-string $controllerClass
     * @param array $defaultActions
     */
    public function __construct(string $controllerClass, array $defaultActions)
    {
        $this->add(PageType::LIST, GlobalAction::create(PageType::CREATE->value, t('New'))
            ->linkToCrud($controllerClass, PageType::CREATE)
            ->displayIf(function () use ($defaultActions): bool {
                return in_array(PageType::CREATE, $defaultActions, true);
            }));

        $this->add(PageType::LIST, DatagridAction::create(PageType::DETAIL->value, t('Detail'))
            ->linkToCrud($controllerClass, PageType::DETAIL)
            ->displayIf(function () use ($defaultActions): bool {
                return in_array(PageType::DETAIL, $defaultActions, true);
            }));

        $this->add(PageType::LIST, DatagridAction::create(PageType::EDIT->value, t('Edit'))
            ->linkToCrud($controllerClass, PageType::EDIT)
            ->displayIf(function () use ($defaultActions): bool {
                return in_array(PageType::EDIT, $defaultActions, true);
            }));

        $this->add(PageType::LIST, DatagridAction::create(PageType::DELETE->value, t('Delete'))
            ->linkToCrud($controllerClass, PageType::DELETE)
            ->displayIf(function () use ($defaultActions): bool {
                return in_array(PageType::DELETE, $defaultActions, true);
            }));

        $backToListAction = GlobalAction::create('backToList', t('Back to list'))
            ->linkToCrud($controllerClass, PageType::LIST);

        $this->add(PageType::EDIT, $backToListAction);
        $this->add(PageType::DETAIL, $backToListAction);
        $this->add(PageType::CREATE, $backToListAction);
    }

    /**
     * Add action to be displayed on specific pageType
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder $actionBuilder
     * @return $this
     */
    public function add(PageType $pageType, AbstractActionBuilder $actionBuilder): self
    {
        $actionData = $actionBuilder->getData();

        Assert::keyNotExists($this->actions[$pageType->value], $actionData->name, 'Action already exists. Use `ActionsConfig::update()` method or create action with different name');

        $this->actions[$pageType->value][$actionData->name] = $actionBuilder;

        return $this;
    }

    /**
     * Update existing action with new configuration
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param string $actionName
     * @param callable $callable
     * @param callable(\Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder $action): \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder $callable
     * @return $this
     */
    public function update(PageType $pageType, string $actionName, callable $callable): self
    {
        Assert::keyExists($this->actions[$pageType->value], $actionName);

        $action = $this->actions[$pageType->value][$actionName];

        $this->actions[$pageType->value][$actionName] = $callable($action);

        return $this;
    }

    /**
     * Remove action
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @param string $actionName
     * @return $this
     */
    public function remove(PageType $pageType, string $actionName): self
    {
        Assert::keyExists($this->actions[$pageType->value], $actionName);

        unset($this->actions[$pageType->value][$actionName]);

        return $this;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractActionBuilder[]
     */
    public function getActions(PageType $pageType): array
    {
        return $this->actions[$pageType->value];
    }
}
