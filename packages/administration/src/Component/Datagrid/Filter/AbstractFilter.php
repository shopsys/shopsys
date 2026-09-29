<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Symfony\Component\String\UnicodeString;

/**
 * The common part of a filter: a fluent declaration (`TextFilter::new('author.fullName', t('Author'))`),
 * the operations narrowed to what the medium can evaluate on the path, and the translation of a submitted
 * rule into a `Comparison`. A type of filter says which operations it offers and how its value is entered.
 *
 * @phpstan-consistent-constructor
 */
abstract class AbstractFilter implements FilterInterface
{
    protected ?string $name = null;

    /**
     * @var string[]|null Null keeps the defaults of the type
     */
    protected ?array $operators = null;

    /**
     * @var array<string, mixed>
     */
    protected array $valueFormOptions = [];

    protected ?FilterEnvironment $environment = null;

    protected ?PathDescription $pathDescription = null;

    /**
     * @var string[]|null The operations left after `resolveFor()`, null before it
     */
    protected ?array $resolvedOperators = null;

    public function __construct(
        protected readonly string $path,
        protected ?string $label = null,
    ) {
    }

    /**
     * @param string $path Dot notation path of the records, the same one the fields use — `email`, `author.fullName`
     * @param string|null $label Shown to the administrator, derived from the last part of the path when not given
     */
    public static function new(string $path, ?string $label = null): static
    {
        return new static($path, $label);
    }

    /**
     * @return string[]
     */
    abstract protected function getDefaultOperators(): array;

    /**
     * Overrides the key of the filter in the request, which is derived from the path by default.
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return $this
     */
    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Narrows the offered operations to a subset of the defaults of the type.
     *
     * @param string[] $operators
     * @return $this
     */
    public function setOperators(array $operators): static
    {
        $this->operators = array_values($operators);

        return $this;
    }

    /**
     * Options merged into those of the value form type — `choices`, `query_builder`, `attr`, ...
     *
     * @param array<string, mixed> $valueFormOptions
     * @return $this
     */
    public function setValueFormOptions(array $valueFormOptions): static
    {
        $this->valueFormOptions = $valueFormOptions;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return $this->name ?? str_replace('.', '_', $this->path);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        if ($this->label !== null) {
            return $this->label;
        }

        $lastPart = substr($this->path, (int)strrpos('.' . $this->path, '.'));

        return ucfirst((string)new UnicodeString($lastPart)->snake()->replace('_', ' '));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOperators(): array
    {
        return $this->resolvedOperators ?? $this->operators ?? $this->getDefaultOperators();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getOperatorLabel(string $operator): string
    {
        return FilterOperatorLabel::get($operator);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(string $operator): array
    {
        return array_replace(['required' => false], $this->getDefaultValueFormOptions($operator), $this->valueFormOptions);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultValueFormOptions(string $operator): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function resolveFor(FilterEnvironment $environment): void
    {
        $this->environment = $environment;

        try {
            $this->pathDescription = $environment->describePath($this->path);
        } catch (PathNotFoundException $exception) {
            throw new FilterNotApplicableException($this->getName(), $exception->getMessage(), $exception);
        }

        $this->assertApplicable($environment);

        $this->resolvedOperators = array_values(array_filter(
            $this->operators ?? $this->getDefaultOperators(),
            fn (string $operator): bool => $this->isOperatorApplicable($operator, $environment),
        ));

        if ($this->resolvedOperators === []) {
            throw new FilterNotApplicableException($this->getName(), sprintf('none of its operations can be evaluated on the path "%s" by the adapter.', $this->path));
        }
    }

    /**
     * A type of filter refuses a path it cannot work with — an entity filter needs an association, ...
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    protected function assertApplicable(FilterEnvironment $environment): void
    {
    }

    protected function isOperatorApplicable(string $operator, FilterEnvironment $environment): bool
    {
        if ($environment->adapter->getExpressionCapabilities()->supportsOperator($operator) === false) {
            return false;
        }

        if ($this->pathDescription === null) {
            return true;
        }

        try {
            $environment->expressionOperatorApplicability->assertApplicable($operator, $this->pathDescription);
        } catch (OperatorNotApplicableToPathException) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildCondition(FilterRuleData $rule): ?ConditionInterface
    {
        $operator = $rule->operator;

        if ($operator === null || in_array($operator, $this->getOperators(), true) === false) {
            return null;
        }

        $value = $this->normalizeValue($operator, $rule->value);

        if ($value === null && $this->getValueArity($operator) !== ExpressionValueArityEnum::NONE) {
            return null;
        }

        return $this->createComparison($operator, $value);
    }

    /**
     * The comparison of the filter path with a normalized value; a type of filter compares something else
     * (the identifier of a related entity, a range of a day) by overriding this.
     */
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        return new Comparison($this->path, $operator, $value);
    }

    /**
     * The value as submitted, in the shape the operator compares — null when nothing usable was given, so
     * that a rule without a value narrows nothing rather than everything.
     *
     * @return mixed Null, a single value, a non-empty list, or a pair of bounds
     */
    protected function normalizeValue(string $operator, mixed $value): mixed
    {
        return match ($this->getValueArity($operator)) {
            ExpressionValueArityEnum::NONE => null,
            ExpressionValueArityEnum::LIST => $this->normalizeList($value),
            ExpressionValueArityEnum::RANGE => $this->normalizeRange($value),
            default => $this->normalizeSingleValue($value),
        };
    }

    protected function normalizeSingleValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === null || $value === '' ? null : $value;
    }

    /**
     * @return list<mixed>|null
     */
    protected function normalizeList(mixed $value): ?array
    {
        if (is_iterable($value) === false) {
            return null;
        }

        $values = array_values(array_filter(
            is_array($value) ? $value : iterator_to_array($value),
            static fn (mixed $item): bool => $item !== null && $item !== '',
        ));

        return $values === [] ? null : $values;
    }

    /**
     * @return array{0: mixed, 1: mixed}|null
     */
    protected function normalizeRange(mixed $value): ?array
    {
        if (is_array($value) === false) {
            return null;
        }

        $from = $this->normalizeSingleValue($value['from'] ?? null);
        $to = $this->normalizeSingleValue($value['to'] ?? null);

        return $from === null || $to === null ? null : [$from, $to];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueArity(string $operator): string
    {
        return $this->getEnvironment()->expressionOperatorEnum->getValueArity($operator);
    }

    protected function getEnvironment(): FilterEnvironment
    {
        return $this->environment ?? throw new LogicException(sprintf('Filter "%s" has to be resolved for a datagrid first, call resolveFor().', $this->getName()));
    }
}
