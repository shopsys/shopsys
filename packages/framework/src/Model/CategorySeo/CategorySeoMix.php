<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class CategorySeoMix
{
    protected ?Flag $flag = null;

    protected ?string $ordering = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    protected array $parameterValues = [];

    public function __construct(
        protected readonly int $domainId,
        protected readonly Category $category,
    ) {
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getFlag(): ?Flag
    {
        return $this->flag;
    }

    public function setFlag(Flag $flag): void
    {
        $this->flag = $flag;
    }

    public function getOrdering(): ?string
    {
        return $this->ordering;
    }

    public function setOrdering(string $ordering): void
    {
        $this->ordering = $ordering;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValues(): array
    {
        return $this->parameterValues;
    }

    public function addParameterValue(ParameterValue $parameterValue): void
    {
        $this->parameterValues[] = $parameterValue;
    }
}
