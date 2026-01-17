<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

class SelectedCategorySeoMixCombination
{
    /**
     * @param int[] $parameterValueIdsByParameterIds
     */
    public function __construct(
        protected readonly int $domainId,
        protected readonly int $categoryId,
        protected readonly ?string $ordering,
        protected readonly ?int $flagId = null,
        protected readonly array $parameterValueIdsByParameterIds = [],
    ) {
    }

    public function getDomainId(): int
    {
        return $this->domainId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getFlagId(): ?int
    {
        return $this->flagId;
    }

    public function getOrdering(): ?string
    {
        return $this->ordering;
    }

    /**
     * @return int[]
     */
    public function getParameterValueIdsByParameterIds(): array
    {
        return $this->parameterValueIdsByParameterIds;
    }
}
