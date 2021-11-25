<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;

class CategorySeoMix
{
    /**
     * @var int
     */
    private $domainId;

    /**
     * @var \App\Model\Category\Category
     */
    private $category;

    /**
     * @var \App\Model\Product\Flag\Flag|null
     */
    private $flag;

    /**
     * @var string|null
     */
    private $ordering;

    /**
     * @var \App\Model\Product\Parameter\ParameterValue[]
     */
    private $parameterValues = [];

    /**
     * @param int $domainId
     * @param \App\Model\Category\Category $category
     */
    public function __construct(int $domainId, Category $category)
    {
        $this->category = $category;
        $this->domainId = $domainId;
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
     * @return \App\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValues(): array
    {
        return $this->parameterValues;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    public function addParameterValue(ParameterValue $parameterValue): void
    {
        $this->parameterValues[] = $parameterValue;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter[] $parameters
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
            $this->flag !== null ? $this->flag->getId() : null,
            $this->ordering,
            $parameterValueIdsByParameterIds
        );
    }
}
