<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\ChoseCategorySeoMixCombinationIsNotValidException;

class ChoseCategorySeoMixCombination
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
     * @param string|null $choseCategorySeoMixCombinationJson
     * @return self|null
     */
    public static function createFromJson(?string $choseCategorySeoMixCombinationJson): ?self
    {
        if ($choseCategorySeoMixCombinationJson === null) {
            return null;
        }

        $choseCategorySeoMixCombinationArray = json_decode($choseCategorySeoMixCombinationJson, true, 512, JSON_THROW_ON_ERROR);

        return self::createFromArray($choseCategorySeoMixCombinationArray);
    }

    /**
     * @param array $choseCategorySeoMixCombinationArray
     * @return self|null
     */
    public static function createFromArray(array $choseCategorySeoMixCombinationArray): ?self
    {
        foreach (['domainId', 'categoryId', 'flagId', 'ordering', 'parameterValueIdsByParameterIds'] as $checkIndex) {
            if (!array_key_exists($checkIndex, $choseCategorySeoMixCombinationArray)) {
                throw new ChoseCategorySeoMixCombinationIsNotValidException(
                    sprintf(
                        'ChoseCategorySeoMixCombinationJson is not valid due to missing %s index',
                        $checkIndex,
                    ),
                );
            }
        }

        return new self(
            $choseCategorySeoMixCombinationArray['domainId'],
            $choseCategorySeoMixCombinationArray['categoryId'],
            $choseCategorySeoMixCombinationArray['ordering'],
            $choseCategorySeoMixCombinationArray['flagId'],
            $choseCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'],
        );
    }

    /**
     * @return array
     */
    public function getInArray(): array
    {
        return self::getChoseCategorySeoMixCombinationArray(
            $this->domainId,
            $this->categoryId,
            $this->flagId,
            $this->ordering,
            $this->parameterValueIdsByParameterIds,
        );
    }

    /**
     * @return string
     */
    public function getInJson(): string
    {
        return json_encode($this->getInArray(), JSON_THROW_ON_ERROR);
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

    /**
     * @param int $domainId
     * @param int $categoryId
     * @param int|null $flagId
     * @param string|null $ordering
     * @param int[] $parameterValueIdsByParameterIds
     * @return array
     */
    public static function getChoseCategorySeoMixCombinationArray(
        int $domainId,
        int $categoryId,
        ?int $flagId,
        ?string $ordering,
        array $parameterValueIdsByParameterIds,
    ): array {
        ksort($parameterValueIdsByParameterIds);

        return [
            'domainId' => $domainId,
            'categoryId' => $categoryId,
            'flagId' => $flagId,
            'ordering' => $ordering,
            'parameterValueIdsByParameterIds' => $parameterValueIdsByParameterIds,
        ];
    }
}
