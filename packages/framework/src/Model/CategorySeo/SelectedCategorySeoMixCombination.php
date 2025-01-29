<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

class SelectedCategorySeoMixCombination
{
    /**
     * @param int $domainId
     * @param int $categoryId
     * @param string|null $ordering
     * @param int|null $flagId
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

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @return int|null
     */
    public function getFlagId(): ?int
    {
        return $this->flagId;
    }

    /**
     * @return string|null
     */
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
