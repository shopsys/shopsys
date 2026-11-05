<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterRuleData;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
    /**
     * The negating operations by their positive counterparts. Over a collection the filter negates the whole
     * condition instead — "tags is none of Sale" means no tag is Sale, not that some tag is another one.
     *
     * @var array<string, string>
     */
    protected const array POSITIVE_OPERATORS = [
        ExpressionOperatorEnum::NOT_EQUALS => ExpressionOperatorEnum::EQUALS,
        ExpressionOperatorEnum::NOT_IN => ExpressionOperatorEnum::IN,
    ];

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
     * Narrows the offered operations to a subset of the defaults of the type — the type knows how to enter and
     * translate only those.
     *
     * @param string[] $operators
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     * @return $this
     */
    public function setOperators(array $operators): static
    {
        $unknownOperators = array_diff($operators, $this->getDefaultOperators());

        if ($unknownOperators !== []) {
            throw new FilterNotApplicableException($this->getName(), sprintf(
                'it offers only "%s", so "%s" cannot be set.',
                implode('", "', $this->getDefaultOperators()),
                implode('", "', $unknownOperators),
            ));
        }

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

        $pathParts = explode('.', $this->path);

        return ucfirst((string)new UnicodeString(end($pathParts))->snake()->replace('_', ' '));
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
        return $this->getEnvironment()->expressionOperatorEnum->getLabel($operator);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(string $arity): string
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(string $arity): array
    {
        return array_replace(['required' => false], $this->getDefaultValueFormOptions($arity), $this->valueFormOptions);
    }

    /**
     * @param string $arity One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum
     * @return array<string, mixed>
     */
    protected function getDefaultValueFormOptions(string $arity): array
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
            throw new FilterNotApplicableException($this->getName(), sprintf('none of its operations is applicable to the path "%s".', $this->path));
        }
    }

    /**
     * The kinds of value the type of filter works with, null for any.
     *
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum[]|null
     */
    protected function getApplicableValueTypes(): ?array
    {
        return null;
    }

    /**
     * A type of filter refuses a path it cannot work with — a numeric filter needs a number, an entity filter
     * an association, ... A path the medium does not describe is left to the declaration.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    protected function assertApplicable(FilterEnvironment $environment): void
    {
        $valueTypes = $this->getApplicableValueTypes();

        if ($valueTypes === null || $this->pathDescription === null || in_array($this->pathDescription->valueType, $valueTypes, true)) {
            return;
        }

        throw new FilterNotApplicableException($this->getName(), sprintf(
            'the path "%s" leads to %s, the filter works with %s only.',
            $this->path,
            $this->pathDescription->valueType->name,
            implode(', ', array_map(static fn (PathValueTypeEnum $valueType): string => $valueType->name, $valueTypes)),
        ));
    }

    protected function isOperatorApplicable(string $operator, FilterEnvironment $environment): bool
    {
        if ($this->pathDescription === null) {
            return true;
        }

        try {
            $environment->expressionOperatorEnum->assertApplicable($operator, $this->pathDescription);
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

        $positiveOperator = static::POSITIVE_OPERATORS[$operator] ?? null;

        if ($positiveOperator !== null && $this->pathDescription?->isToMany() === true) {
            $condition = $this->createComparison($positiveOperator, $value);

            return $condition === null ? null : Condition::not($condition);
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
