<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

use Closure;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\Action;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Webmozart\Assert\Assert;

class ActionsConfig
{
    /**
     * @var \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction[][]
     */
    private array $actions = [
        ActionType::CREATE->value => [],
        ActionType::EDIT->value => [],
        ActionType::LIST->value => [],
        ActionType::DETAIL->value => [],
    ];

    /**
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController> $controllerClass
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType[] $defaultActions
     */
    public function __construct(string $controllerClass, array $defaultActions)
    {
        $this->add(
            ActionType::LIST,
            Action::create(ActionType::CREATE->value, t('New'))
                ->linkToCrud($controllerClass, ActionType::CREATE)
                ->setIcon('circle-plus')
                ->displayIf(function () use ($defaultActions): bool {
                    return in_array(ActionType::CREATE, $defaultActions, true);
                }),
        );


        $backToListAction = Action::create('backToList', t('Back to overview'))
            ->linkToCrud($controllerClass, ActionType::LIST)
            ->setIcon('arrow-back');

        $this->add(ActionType::EDIT, $backToListAction);
        $this->add(ActionType::DETAIL, $backToListAction);
        $this->add(ActionType::CREATE, $backToListAction);
    }

    /**
     * Add action to be displayed on specific page (ActionType)
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction $actionBuilder
     * @return $this
     */
    public function add(ActionType $actionType, AbstractAction $actionBuilder): self
    {
        $actionData = $actionBuilder->getData();

        Assert::keyNotExists($this->actions[$actionType->value], $actionData->name, 'Action already exists. Use `ActionsConfig::update()` method or create action with different name');

        $this->actions[$actionType->value][$actionData->name] = $actionBuilder;

        return $this;
    }

    /**
     * Update existing action with new configuration
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param string $actionName
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction $action): \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction $callable
     * @return $this
     */
    public function update(ActionType $actionType, string $actionName, Closure $callable): self
    {
        Assert::keyExists($this->actions[$actionType->value], $actionName);

        $action = $this->actions[$actionType->value][$actionName];

        $this->actions[$actionType->value][$actionName] = $callable($action);

        return $this;
    }

    /**
     * Remove action
     *
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @param string $actionName
     * @return $this
     */
    public function remove(ActionType $actionType, string $actionName): self
    {
        Assert::keyExists($this->actions[$actionType->value], $actionName);

        unset($this->actions[$actionType->value][$actionName]);

        return $this;
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\ActionType $actionType
     * @return \Shopsys\AdministrationBundle\Component\Config\Action\Builder\AbstractAction[]
     */
    public function getActions(ActionType $actionType): array
    {
        return $this->actions[$actionType->value];
    }
}
