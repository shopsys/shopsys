<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Stringable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Webmozart\Assert\Assert;

/**
 * A filter over a related entity — the path ends with an association rather than a value:
 *
 *     EntityFilter::new('author.publisher', t('Publisher'))
 *
 * The related entities are offered in a select; the class is learnt from the medium, or given by
 * `setEntityClass()` when the medium has no schema. The condition compares the identifier of the
 * association, so no join is needed.
 */
class EntityFilter extends AbstractFilter
{
    protected const string IDENTIFIER_FIELD = 'id';

    /**
     * @var class-string|null
     */
    protected ?string $entityClass = null;

    /**
     * @param class-string $entityClass
     * @return $this
     */
    public function setEntityClass(string $entityClass): static
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::IN,
            ExpressionOperatorEnum::NOT_IN,
            ExpressionOperatorEnum::IS_NULL,
            ExpressionOperatorEnum::IS_NOT_NULL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(string $operator): string
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultValueFormOptions(string $operator): array
    {
        return [
            'class' => $this->getEntityClass(),
            'choice_label' => $this->describeEntity(...),
            'multiple' => $this->getValueArity($operator) === ExpressionValueArityEnum::LIST,
            'placeholder' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function assertApplicable(FilterEnvironment $environment): void
    {
        if ($this->pathDescription !== null && $this->pathDescription->valueType !== PathValueTypeEnum::ASSOCIATION) {
            throw new FilterNotApplicableException($this->getName(), sprintf('the path "%s" has to end with an association, it leads to %s.', $this->path, $this->pathDescription->valueType->name));
        }

        if ($this->getEntityClass() === null) {
            throw new FilterNotApplicableException($this->getName(), 'the adapter does not tell which entity the path leads to, set it by setEntityClass().');
        }
    }

    /**
     * @return class-string|null
     */
    protected function getEntityClass(): ?string
    {
        return $this->entityClass ?? $this->pathDescription?->targetEntityClass;
    }

    /**
     * A related entity is compared by its identifier on the association itself; a missing relation is
     * tested on the association as well.
     */
    #[Override]
    protected function createComparison(string $operator, mixed $value): ?ConditionInterface
    {
        if ($this->getValueArity($operator) === ExpressionValueArityEnum::NONE) {
            return new Comparison($this->path, $operator);
        }

        return new Comparison(
            $this->path . '.' . static::IDENTIFIER_FIELD,
            $operator,
            is_array($value) ? array_map($this->getIdentifier(...), $value) : $this->getIdentifier($value),
        );
    }

    /**
     * How a related entity reads in the select — the way the administration presents it, its name, or as a
     * last resort its identifier. Override for anything more specific, or pass `choice_label` in the form options.
     */
    protected function describeEntity(object $entity): string
    {
        if ($entity instanceof Presentable) {
            return $entity->toHumanReadable();
        }

        if (method_exists($entity, 'getName')) {
            return (string)$entity->getName();
        }

        if ($entity instanceof Stringable) {
            return (string)$entity;
        }

        return sprintf('#%s', $this->getIdentifier($entity));
    }

    protected function getIdentifier(object $entity): int|string
    {
        Assert::methodExists($entity, 'getId', sprintf('The entities of "%s" have to expose getId() to be filtered by.', $this->getName()));

        return $entity->getId();
    }
}
