<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Search\Exception\FilterNotConfiguredException;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Generic advanced search filter configured fluently in the controller.
 * For a filter reused across controllers, implement FilterInterface in a dedicated class instead.
 */
final class Filter implements FilterInterface
{
    /**
     * @var \Shopsys\AdministrationBundle\Component\Search\Operator[]
     */
    private array $operators = [Operator::CONTAINS, Operator::NOT_CONTAINS];

    /**
     * @var class-string<\Symfony\Component\Form\FormTypeInterface>
     */
    private string $valueFormType = TextType::class;

    /**
     * @var array<string, mixed>
     */
    private array $valueFormOptions = [];

    private ?Closure $applyCallback = null;

    private function __construct(
        private readonly string $name,
        private readonly string $label,
    ) {
    }

    public static function create(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function withOperators(Operator ...$operators): self
    {
        $this->operators = array_values($operators);

        return $this;
    }

    /**
     * @param class-string<\Symfony\Component\Form\FormTypeInterface> $valueFormType
     * @param array<string, mixed> $valueFormOptions
     */
    public function withFormType(string $valueFormType, array $valueFormOptions = []): self
    {
        $this->valueFormType = $valueFormType;
        $this->valueFormOptions = $valueFormOptions;

        return $this;
    }

    /**
     * Sets the query logic of the filter, called once with all rules of this subject.
     *
     * @param \Closure(\Doctrine\ORM\QueryBuilder, \Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection): void $applyCallback
     */
    public function apply(Closure $applyCallback): self
    {
        $this->applyCallback = $applyCallback;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return $this->operators;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): string
    {
        return $this->valueFormType;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return $this->valueFormOptions;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        if ($this->applyCallback === null) {
            throw FilterNotConfiguredException::noApplyCallback($this->name);
        }

        ($this->applyCallback)($queryBuilder, $rules);
    }
}
