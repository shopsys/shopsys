<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action\Builder;

use Closure;
use InvalidArgumentException;
use function sprintf;

abstract class AbstractAction
{
    protected ?string $label = null;

    protected ?string $icon = null;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [
        'class' => 'btn wrap-bar__btn',
    ];

    /**
     * Forbidden attributes that can not be set by user. If user tries to set them, exception will be thrown.
     * Key is attribute name, value is message that will be shown in exception.
     *
     * @var array<string, ?string>
     */
    protected array $forbiddenAttributes = [];

    /**
     * @var null|\Closure(?object $entity): bool
     */
    protected ?Closure $displayIf = null;

    /**
     * @param string $name
     * @param string $label
     * @return $this
     */
    abstract public static function create(string $name, string $label): self;

    /**
     * @return string
     */
    abstract protected function getTemplate(): string;

    /**
     * @param object|null $entity
     * @return array<string, mixed>
     */
    abstract protected function getTemplateParameters(?object $entity): array;

    /**
     * @param string $name
     * @param string $label
     */
    protected function __construct(protected string $name, string $label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name of action that will be shown to the users
     *
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set icon of action that will be shown next to label
     *
     * @param string $icon
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set function that will determine if action should be displayed
     *
     * @param \Closure(?object $entity): bool $function Function must return boolean value. If function returns false, action will not be displayed
     * @return $this
     */
    public function displayIf(Closure $function): self
    {
        $this->displayIf = $function;

        return $this;
    }

    /**
     * Set attribute that will be passed to the template. This can be used to set for example `data-` attributes or change CSS classes.
     * If value is null, attribute will be removed.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $append If true, value will be appended to existing value. If false, existing value will be replaced.
     * @return $this
     */
    public function setAttribute(string $name, mixed $value, bool $append = false): self
    {
        if (array_key_exists($name, $this->forbiddenAttributes)) {
            throw new InvalidArgumentException(sprintf('Attribute "%s" is forbidden to set. %s', $name, $this->forbiddenAttributes[$name]));
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

    /**
     * @param object|null $entity
     * @return array|null
     */
    public function renderData(?object $entity): ?array
    {
        if ($this->displayIf !== null && call_user_func($this->displayIf, $entity) === false) {
            return null;
        }

        return [
            'template' => $this->getTemplate(),
            'parameters' => [
                ...$this->getTemplateParameters($entity),
                'attributes' => $this->parseAttributesToHTML(),
            ],
        ];
    }

    /**
     * @return string
     */
    private function parseAttributesToHTML(): string
    {
        return array_reduce(
            array_keys($this->attributes),
            function (string $carry, string $key) {
                $value = $this->attributes[$key];

                if ($value === null) {
                    $value = false;
                }

                if ($value === true && str_starts_with($key, 'aria-')) {
                    $value = 'true';
                }

                return match ($value) {
                    true => "{$carry} {$key}",
                    false => $carry,
                    default => sprintf('%s %s="%s"', $carry, $key, $value),
                };
            },
            '',
        );
    }
}
