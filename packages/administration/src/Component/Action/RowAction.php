<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\Grid\GridRowActionInterface;

/**
 * This class is used for defining row actions in grid.
 */
final class RowAction extends AbstractAction implements GridRowActionInterface
{
    use ActionRouteTrait;

    private const string DEFAULT_CLASSES = 'in-icon in-icon--edit table-action';

    private bool $renderTooltip = true;

    private ?string $additionalClass = null;

    private ?string $confirmMessage = null;

    /**
     * Sets additional classes for row action. Use this method to add custom classes. Default classes are required for proper functionality.
     *
     * @param string $additionalClass
     * @return self
     */
    public function setAdditionalClass(string $additionalClass): self
    {
        $this->additionalClass = $additionalClass;

        return $this;
    }

    /**
     * If you want to show confirm dialog before action is executed, use this method to set confirm message.
     *
     * @param string $confirmMessage
     * @return self
     */
    public function setConfirmMessage(string $confirmMessage): self
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    /**
     * In case you want to disable tooltip for row action, use this method. Tooltip is enabled by default.
     *
     * @param bool $render
     * @return self
     */
    public function showTooltip(bool $render = true): self
    {
        $this->renderTooltip = $render;

        return $this;
    }

    /**
     * @param string $name
     * @param string $label
     * @param string|null $icon
     * @return self
     */
    public static function create(string $name, string $label, ?string $icon = null): self
    {
        if ($icon === null) {
            throw new InvalidArgumentException('Icon is required for row action. ');
        }

        return new self($name, $label, $icon);
    }

    /**
     * Validate action configuration before rendering
     *
     * @param mixed $data
     * @return bool
     */
    public function validate(mixed $data): bool
    {
        if ($this->actionRoute === null) {
            throw new InvalidArgumentException('Route must be set for row action. Use one of the "linkTo*" methods.');
        }

        return parent::validate($data);
    }

    /**
     * @return string
     */
    protected function getTemplate(): string
    {
        return '@ShopsysAdministration/datagrid/row_action.html.twig';
    }

    /**
     * @return array
     */
    protected function getTemplateParameters(): array
    {
        $this->attributes['class'] = self::DEFAULT_CLASSES;

        if ($this->openInNewTab === true) {
            $this->attributes['target'] = '_blank';
        }

        if ($this->renderTooltip) {
            $this->attributes['class'] .= ' js-tooltip';
            $this->attributes['data-toggle'] = 'tooltip';
            $this->attributes['data-placement'] = 'left';
            $this->attributes['title'] = '';
            $this->attributes['data-original-title'] = $this->label;
        } else {
            $this->attributes['title'] = $this->label;
        }

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
    protected function getForbiddenAttributes(): array
    {
        return [
            ...$this->actionRouteForbiddenAttributes,
            'class' => 'There are specific classes that are required for proper functionality of row action. Use "setAdditionalClass" method to add custom classes.',
            'title' => 'Title is set automatically based on label.',

            // Tooltip attributes
            'data-toggle' => 'Tooltip is enabled by default. Use "showTooltip" method to disable it.',
            'data-placement' => 'Tooltip is enabled by default. Use "showTooltip" method to disable it.',
            'data-original-title' => 'Tooltip is enabled by default. Use "showTooltip" method to disable it.',

            // Confirm dialog attributes
            'data-confirm-message' => 'Use "setConfirmMessage" method to set confirm message.',
            'data-confirm-window' => 'Use "setConfirmMessage" method to set confirm message.',
        ];
    }
}
