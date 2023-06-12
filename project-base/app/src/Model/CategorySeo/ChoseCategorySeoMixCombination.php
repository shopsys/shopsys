<?php

declare(strict_types=1);

namespace App\Model\CategorySeo;

use App\Model\CategorySeo\Exception\ChoseCategorySeoMixCombinationIsNotValidException;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class ChoseCategorySeoMixCombination
{
    private string $ordering;

    /**
     * @param int $domainId
     * @param int $categoryId
     * @param int $flagId
     * @param string $ordering
     * @param int[] $parameterValueIdsByParameterIds
     */
    public function __construct(
        private int $domainId,
        private int $categoryId,
        private ?int $flagId = null,
        ?string $ordering,
        private array $parameterValueIdsByParameterIds = [],
    ) {
        $this->ordering = $ordering;
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

        $choseCategorySeoMixCombinationArray = json_decode($choseCategorySeoMixCombinationJson, true);

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
            $choseCategorySeoMixCombinationArray['flagId'],
            $choseCategorySeoMixCombinationArray['ordering'],
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
        return json_encode($this->getInArray());
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
     * @return string
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
