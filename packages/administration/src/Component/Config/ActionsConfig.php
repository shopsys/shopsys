<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

use Closure;
use Shopsys\AdministrationBundle\Component\Action\AbstractAction;
use Shopsys\AdministrationBundle\Component\Action\Action;
use Webmozart\Assert\Assert;

class ActionsConfig
{
    /**
     * @var \Shopsys\AdministrationBundle\Component\Action\AbstractAction[][]
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
            Action::create(ActionType::CREATE->value, t('Create new item'))
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
     */
    public function add(ActionType $actionType, AbstractAction $action): static
    {
        Assert::keyNotExists($this->actions[$actionType->value], $action->getName(), 'Action already exists. Use `ActionsConfig::update()` method or create action with different name');

        $this->actions[$actionType->value][$action->getName()] = $action;

        return $this;
    }

    /**
     * Update existing action with new configuration
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Action\AbstractAction): \Shopsys\AdministrationBundle\Component\Action\AbstractAction $callable
     */
    public function update(ActionType $actionType, string $actionName, Closure $callable): static
    {
        Assert::keyExists($this->actions[$actionType->value], $actionName);

        $action = $this->actions[$actionType->value][$actionName];

        $this->actions[$actionType->value][$actionName] = $callable($action);

        return $this;
    }

    /**
     * Remove action
     */
    public function remove(ActionType $actionType, string $actionName): static
    {
        Assert::keyExists($this->actions[$actionType->value], $actionName);

        unset($this->actions[$actionType->value][$actionName]);

        return $this;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Action\AbstractAction[]
     */
    public function getActions(ActionType $actionType): array
    {
        return $this->actions[$actionType->value];
    }
}
