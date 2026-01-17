<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface;

/**
 * This class is used for defining row actions in grid.
 */
final class RowAction extends AbstractRoutableAction implements GridRowActionInterface
{
    private const string DEFAULT_CLASSES = 'px-1 link-secondary';

    private bool $renderTooltip = true;

    private ?string $additionalClass = null;

    private ?string $confirmMessage = null;

    /**
     * Sets additional classes for row action. Use this method to add custom classes. Default classes are required for proper functionality.
     */
    public function setAdditionalClass(string $additionalClass): self
    {
        $this->additionalClass = $additionalClass;

        return $this;
    }

    /**
     * If you want to show confirm dialog before action is executed, use this method to set confirm message.
     */
    public function setConfirmMessage(string $confirmMessage): self
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    /**
     * In case you want to disable tooltip for row action, use this method. Tooltip is enabled by default.
     */
    public function showTooltip(bool $render = true): self
    {
        $this->renderTooltip = $render;

        return $this;
    }

    #[Override]
    public static function create(string $name, string $label, ?string $icon = null): static
    {
        if ($icon === null) {
            throw new InvalidArgumentException('Icon is required for row action. ');
        }

        return new self($name, $label, $icon);
    }

    /**
     * Validate action configuration before rendering
     */
    #[Override]
    public function validate(mixed $data): bool
    {
        if ($this->actionRoute === null) {
            throw new InvalidArgumentException('Route must be set for row action. Use one of the "linkTo*" methods.');
        }

        return parent::validate($data);
    }

    #[Override]
    protected function getTemplate(): string
    {
        return '@ShopsysAdministration/datagrid/row_action.html.twig';
    }

    #[Override]
    protected function getTemplateParameters(): array
    {
        $this->prepareRoutableAttributes();

        $this->attributes['class'] = self::DEFAULT_CLASSES;

        if ($this->renderTooltip) {
            $this->attributes['data-bs-toggle'] = 'tooltip';
            $this->attributes['data-bs-placement'] = 'left';
        }

        $this->attributes['title'] = $this->label;

        if ($this->additionalClass !== null) {
            $this->attributes['class'] .= ' ' . $this->additionalClass;
        }

        if ($this->confirmMessage) {
            $this->attributes['data-confirm-message'] = $this->confirmMessage;
            $this->attributes['data-confirm-window'] = true;
        }

        return [
            'name' => $this->name,
            'label' => $this->label,
            'icon' => $this->icon,
            'actionRoute' => $this->actionRoute,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    #[Override]
    protected function getForbiddenAttributes(): array
    {
        return [
            ...parent::getForbiddenAttributes(),
            'class' => 'There are specific classes that are required for proper functionality of row action. Use "setAdditionalClass" method to add custom classes.',
            'title' => 'Title is set automatically based on label.',

            // Tooltip attributes
            'data-bs-toggle' => 'Tooltip is enabled by default. Use "showTooltip" method to disable it.',
            'data-bs-placement' => 'Tooltip is enabled by default. Use "showTooltip" method to disable it.',

            // Confirm dialog attributes
            'data-confirm-message' => 'Use "setConfirmMessage" method to set confirm message.',
            'data-confirm-window' => 'Use "setConfirmMessage" method to set confirm message.',
        ];
    }
}
