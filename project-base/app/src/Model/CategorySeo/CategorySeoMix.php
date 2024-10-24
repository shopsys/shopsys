<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\Category\Category;
use App\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class CategorySeoMix
{
    private ?Flag $flag = null;

    private ?string $ordering = null;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    private array $parameterValues = [];

    /**
     * @param int $domainId
     * @param \App\Model\Category\Category $category
     */
    public function __construct(
        private readonly int $domainId,
        protected readonly Category $category,
    ) {
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return \App\Model\Category\Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return \App\Model\Product\Flag\Flag|null
     */
    public function getFlag(): ?Flag
    {
        return $this->flag;
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     */
    public function setFlag(Flag $flag): void
    {
        $this->flag = $flag;
    }

    /**
     * @return string|null
     */
    public function getOrdering(): ?string
    {
        return $this->ordering;
    }

    /**
     * @param string $ordering
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     */
    public function addParameterValue(ParameterValue $parameterValue): void
    {
        $this->parameterValues[] = $parameterValue;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] $parameters
     * @return \App\Model\CategorySeo\ChoseCategorySeoMixCombination
     */
    public function getChoseCategorySeoMixCombination(array $parameters): ChoseCategorySeoMixCombination
    {
        $parameterValueIdsByParameterIds = [];

        foreach ($this->getParameterValues() as $index => $parameterValue) {
            $parameterValueIdsByParameterIds[$parameters[$index]->getId()] = $parameterValue->getId();
        }

        return new ChoseCategorySeoMixCombination(
            $this->getDomainId(),
            $this->category->getId(),
            $this->ordering,
            $this->flag?->getId(),
            $parameterValueIdsByParameterIds,
        );
    }
}
