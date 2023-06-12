<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Component\UploadedFile\UploadedFileFacade;
use App\Model\Category\Category;
use App\Model\Category\CategoryParameterRepository;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade as BaseParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\Parameter create(\App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 * @method \App\Model\Product\Parameter\Parameter edit(int $parameterId, \App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\ParameterValue getParameterValueByValueTextAndLocale(string $valueText, string $locale)
 * @method dispatchParameterEvent(\App\Model\Product\Parameter\Parameter $parameter, string $eventType)
 * @method \App\Model\Product\Parameter\Parameter getByUuid(string $uuid)
 * @method \App\Model\Product\Parameter\ParameterValue getParameterValueByUuid(string $uuid)
 * @method \App\Model\Product\Parameter\Parameter[] getParametersByUuids(string[] $uuids)
 * @method \App\Model\Product\Parameter\ParameterValue[] getParameterValuesByUuids(string[] $uuids)
 */
class ParameterFacade extends BaseParameterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \App\Model\Category\CategoryParameterRepository $categoryParameterRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ParameterFactoryInterface $parameterFactory,
        EventDispatcherInterface $eventDispatcher,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        private readonly UploadedFileFacade $uploadedFileFacade,
        private readonly CategoryParameterRepository $categoryParameterRepository,
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $parameterFactory,
            $eventDispatcher,
        );
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByAkeneoCode(string $akeneoCode): ?Parameter
    {
        return $this->parameterRepository->findParameterByAkeneoCode($akeneoCode);
    }

    /**
     * @param int[] $parameterValueIdsByParameterId
     * @return string[]
     */
    public function getParameterValueNamesIndexedByParameterNames(array $parameterValueIdsByParameterId): array
    {
        $parameterValueNamesIndexedByParameterNames = [];
        foreach ($parameterValueIdsByParameterId as $parameterId => $parameterValueId) {
            $parameter = $this->getById((int)$parameterId);
            $parameterValue = $this->parameterRepository->getParameterValueById((int)$parameterValueId);

            $parameterValueNamesIndexedByParameterNames[$parameter->getName()] = $parameterValue->getText();
        }

        return $parameterValueNamesIndexedByParameterNames;
    }

    /**
     * @param int $parameterValueId
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        return $this->parameterRepository->getParameterValueById($parameterValueId);
    }

    /**
     * @param string $parameterValueText
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue|null
     */
    public function findParameterValueByText(string $parameterValueText, string $locale): ?ParameterValue
    {
        return $this->parameterRepository->findParameterValueByText($parameterValueText, $locale);
    }

    /**
     * @param int $parameterId
     */
    public function deleteById($parameterId): void
    {
        $parameter = $this->parameterRepository->getById($parameterId);
        $this->readyCategorySeoMixFacade->deleteAllWithParameter($parameter);

        parent::deleteById($parameterId);
    }

    /**
     * @return int[]
     */
    public function getAllAkeneoParameterIds(): array
    {
        return $this->parameterRepository->getAllAkeneoParameterIds();
    }

    /**
     * @param int $parameterValueId
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function editParameterValue(int $parameterValueId, ParameterValueData $parameterValueData): ParameterValue
    {
        $parameterValue = $this->parameterRepository->getParameterValueById($parameterValueId);
        $parameterValue->edit($parameterValueData);

        if ($parameterValueData->colourIcon->uploadedFilenames) {
            $this->uploadedFileFacade->manageSingleFile($parameterValue, $parameterValueData->colourIcon);
        }

        if (count($parameterValueData->colourIcon->uploadedFilenames) === 0 && $parameterValueData->colourIcon->filesToDelete) {
            $this->uploadedFileFacade->deleteAllUploadedFilesByEntity($parameterValue);
        }

        $this->em->flush();

        return $parameterValue;
    }

    /**
     * @param int[][] $parameterValueIdsIndexedByParameterId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    public function getParameterFilterChoicesByIds(array $parameterValueIdsIndexedByParameterId, string $locale): array
    {
        $parameterValueIds = array_reduce($parameterValueIdsIndexedByParameterId, 'array_merge', []);
        $allParameters = $this->parameterRepository->getVisibleParametersByIds(
            array_keys($parameterValueIdsIndexedByParameterId),
            $locale,
        );
        $allParameterValues = $this->parameterRepository->getParameterValuesByIds($parameterValueIds);

        $parameterFilterChoices = [];

        foreach ($allParameters as $parameter) {
            $valueIdsForParameter = $parameterValueIdsIndexedByParameterId[$parameter->getId()];
            $parameterValues = array_intersect_key($allParameterValues, array_flip($valueIdsForParameter));

            uasort($parameterValues, function (ParameterValue $first, ParameterValue $second) {
                return strcmp($first->getText(), $second->getText());
            });

            $parameterFilterChoices[] = new ParameterFilterChoice(
                $parameter,
                $parameterValues,
            );
        }

        return $parameterFilterChoices;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return int[]
     */
    public function getParametersIdsSortedByPositionFilteredByCategory(Category $category): array
    {
        return array_map(
            function ($categoryParameter) {
                return $categoryParameter->getParameter()->getId();
            },
            $this->categoryParameterRepository->getCategoryParametersByCategorySortedByPosition($category),
        );
    }
}
