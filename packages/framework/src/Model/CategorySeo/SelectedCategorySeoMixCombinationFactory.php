<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Model\CategorySeo\Exception\SelectedCategorySeoMixCombinationIsNotValidException;

class SelectedCategorySeoMixCombinationFactory
{
    /**
     * @param int[] $parameterValueIdsByParameterIds
     */
    public function create(
        int $domainId,
        int $categoryId,
        ?int $flagId,
        ?string $ordering,
        array $parameterValueIdsByParameterIds,
    ): SelectedCategorySeoMixCombination {
        ksort($parameterValueIdsByParameterIds);

        return new SelectedCategorySeoMixCombination(
            $domainId,
            $categoryId,
            $ordering,
            $flagId,
            $parameterValueIdsByParameterIds,
        );
    }

    public function createFromJson(string $selectedCategorySeoMixCombinationJson): SelectedCategorySeoMixCombination
    {
        $selectedCategorySeoMixCombinationArray = json_decode($selectedCategorySeoMixCombinationJson, true, 512, JSON_THROW_ON_ERROR);

        return $this->createFromArray($selectedCategorySeoMixCombinationArray);
    }

    public function createFromArray(array $selectedCategorySeoMixCombinationArray): SelectedCategorySeoMixCombination
    {
        foreach (['domainId', 'categoryId', 'flagId', 'ordering', 'parameterValueIdsByParameterIds'] as $checkIndex) {
            if (!array_key_exists($checkIndex, $selectedCategorySeoMixCombinationArray)) {
                throw new SelectedCategorySeoMixCombinationIsNotValidException(
                    sprintf(
                        'SelectedCategorySeoMixCombinationJson is not valid due to missing %s index',
                        $checkIndex,
                    ),
                );
            }
        }

        return new SelectedCategorySeoMixCombination(
            $selectedCategorySeoMixCombinationArray['domainId'],
            $selectedCategorySeoMixCombinationArray['categoryId'],
            $selectedCategorySeoMixCombinationArray['ordering'],
            $selectedCategorySeoMixCombinationArray['flagId'],
            $selectedCategorySeoMixCombinationArray['parameterValueIdsByParameterIds'],
        );
    }

    /**
     * @param int[] $parameterValueIdsByParameterIds
     */
    public function createArray(
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

    public function createArrayFromSelectedCategorySeoMixCombination(
        SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination,
    ): array {
        return $this->createArray(
            $selectedCategorySeoMixCombination->getDomainId(),
            $selectedCategorySeoMixCombination->getCategoryId(),
            $selectedCategorySeoMixCombination->getFlagId(),
            $selectedCategorySeoMixCombination->getOrdering(),
            $selectedCategorySeoMixCombination->getParameterValueIdsByParameterIds(),
        );
    }

    public function createJsonFromSelectedCategorySeoMixCombination(
        SelectedCategorySeoMixCombination $selectedCategorySeoMixCombination,
    ): string {
        $selectedCategorySeoMixCombinationArray = $this->createArrayFromSelectedCategorySeoMixCombination($selectedCategorySeoMixCombination);

        return json_encode($selectedCategorySeoMixCombinationArray, JSON_THROW_ON_ERROR);
    }
}
