<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Action;

use Closure;
use InvalidArgumentException;
use function sprintf;

abstract class AbstractAction
{
    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [
        'class' => 'btn',
    ];

    /**
     * @var null|\Closure(mixed): bool
     */
    protected ?Closure $displayIf = null;

    /**
     * Set name of action that will be shown to the users
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set icon of action that will be shown next to label
     */
    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set function that will determine if action should be displayed
     *
     * @param \Closure(mixed): bool $function Function must return boolean value. If function returns false, action will not be displayed.
     *      The closure receives the data the action is built with, depending on the action kind:
     *      a top action (Action) gets the data of the current page — the entity on the edit page, null on the list, create, and detail pages;
     *      a row action (RowAction) gets the data of the grid row it is rendered in
     */
    public function displayIf(Closure $function): static
    {
        $this->displayIf = $function;

        return $this;
    }

    /**
     * Set attribute that will be passed to the template. This can be used to set for example `data-` attributes or change CSS classes.
     * If value is null, attribute will be removed.
     *
     * @param bool $append If true, value will be appended to existing value. If false, existing value will be replaced.
     */
    public function setAttribute(string $name, mixed $value, bool $append = false): static
    {
        if (array_key_exists($name, $this->getForbiddenAttributes())) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" is forbidden to set. %s', $name, $this->getForbiddenAttributes()[$name]));
        }

        if ($value === null) {
            unset($this->attributes[$name]);

            return $this;
        }

        if ($append && array_key_exists($name, $this->attributes) === true) {
            $this->attributes[$name] .= ' ' . $value;
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }

    abstract public static function create(string $name, string $label, ?string $icon): static;

    protected function __construct(protected string $name, protected string $label, protected ?string $icon = null)
    {
    }

    abstract protected function getTemplate(): string;

    /**
     * @return array<string, mixed>
     */
    abstract protected function getTemplateParameters(): array;

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Forbidden attributes that can not be set by user. If user tries to set them, exception will be thrown.
     * Key is attribute name, value is message that will be shown in exception.
     *
     * @return array<string, string|null>
     */
    protected function getForbiddenAttributes(): array
    {
        return [];
    }

    /**
     * Prepare action configuration before rendering
     */
    protected function prepareAction(mixed $data): bool
    {
        return $this->displayIf === null || call_user_func($this->displayIf, $data) !== false;
    }

    final public function build(mixed $data): array|null
    {
        // Clone action for build call to avoid state pollution between multiple renders
        $action = clone $this;

        if ($action->prepareAction($data) === false) {
            return null;
        }

        return [
            'template' => $action->getTemplate(),
            'parameters' => [
                ...$action->getTemplateParameters(),
                'attributes' => $action->parseAttributesToHTML(),
            ],
        ];
    }

    private function parseAttributesToHTML(): string
    {
        $html = '';

        foreach ($this->attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            if ($value === true && str_starts_with($key, 'aria-') === true) {
                $html = sprintf('%s %s="%s"', $html, $key, 'true');

                continue;
            }

            if ($value === true) {
                $html = "{$html} {$key}";

                continue;
            }

            $html = sprintf('%s %s="%s"', $html, $key, htmlspecialchars((string)$value, ENT_QUOTES));
        }

        return $html;
    }
}
