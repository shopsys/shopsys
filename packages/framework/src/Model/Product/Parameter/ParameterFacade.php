<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository;
use Shopsys\FrameworkBundle\Model\CategorySeo\DeleteReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParameterFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly ParameterFactory $parameterFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CategoryParameterRepository $categoryParameterRepository,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        protected readonly ParameterValueFactory $parameterValueFactory,
        protected readonly DeleteReadyCategorySeoMixFacade $deleteReadyCategorySeoMixFacade,
        protected readonly ParameterSortingHelper $parameterSortingHelper,
    ) {
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getById($parameterId)
    {
        return $this->parameterRepository->getById($parameterId);
    }

    public function getByUuid(string $uuid): Parameter
    {
        return $this->parameterRepository->getByUuid($uuid);
    }

    public function getParameterValueByUuid(string $uuid): ParameterValue
    {
        return $this->parameterRepository->getParameterValueByUuid($uuid);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAll()
    {
        return $this->parameterRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAllWithTranslations(string $locale): array
    {
        return $this->parameterRepository->getAllWithTranslations($locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function create(ParameterData $parameterData)
    {
        $parameter = $this->parameterFactory->create($parameterData);
        $this->em->persist($parameter);
        $this->em->flush();

        $this->dispatchParameterEvent($parameter, ParameterEvent::CREATE);

        return $parameter;
    }

    /**
     * @param string[] $namesByLocale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByNames(array $namesByLocale)
    {
        return $this->parameterRepository->findParameterByNames($namesByLocale);
    }

    public function existsParameterByName(string $name, string $locale, ?Parameter $excludeParameter = null): bool
    {
        return $this->parameterRepository->existsParameterByName($name, $locale, $excludeParameter);
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function edit($parameterId, ParameterData $parameterData)
    {
        $parameter = $this->parameterRepository->getById($parameterId);
        $parameter->edit($parameterData);
        $this->em->flush();

        $this->dispatchParameterEvent($parameter, ParameterEvent::UPDATE);

        return $parameter;
    }

    /**
     * @param int $parameterId
     */
    public function deleteById($parameterId)
    {
        $parameter = $this->parameterRepository->getById($parameterId);

        $this->deleteReadyCategorySeoMixFacade->deleteAllWithParameter($parameter);

        $this->em->remove($parameter);

        $this->dispatchParameterEvent($parameter, ParameterEvent::DELETE);

        $this->em->flush();
    }

    public function getParameterValueByValueTextNumericValueAndLocale(
        string $valueText,
        ?string $numericValue,
        string $locale,
    ): ParameterValue {
        return $this->parameterRepository->getParameterValueByValueTextNumericValueAndLocale($valueText, $numericValue, $locale);
    }

    public function findParameterValueByValueTextNumericValueAndLocale(
        string $valueText,
        ?string $numericValue,
        string $locale,
    ): ?ParameterValue {
        return $this->parameterRepository->findParameterValueByValueTextNumericValueAndLocale($valueText, $numericValue, $locale);
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
     * @see \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent class
     */
    protected function dispatchParameterEvent(Parameter $parameter, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new ParameterEvent($parameter), $eventType);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     */
    protected function dispatchParameterValueEvent(array $parameterValues): void
    {
        $this->eventDispatcher->dispatch(new ParameterValueEvent($parameterValues), ParameterValueEvent::UPDATE);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersByUuids(array $uuids): array
    {
        return $this->parameterRepository->getParametersByUuids($uuids);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByUuids(array $uuids): array
    {
        return $this->parameterRepository->getParameterValuesByUuids($uuids);
    }

    /**
     * @param int[][] $parameterValueIdsIndexedByParameterId
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

            $parameterFilterChoices[] = new ParameterFilterChoice(
                $parameter,
                $this->parameterSortingHelper->sortParameterValuesAlphabetically($parameterValues, $locale),
            );
        }

        return $parameterFilterChoices;
    }

    /**
     * @return int[]
     */
    public function getParametersIdsSortedByPositionFilteredByCategory(Category $category): array
    {
        return array_map(
            static fn ($categoryParameter) => $categoryParameter->getParameter()->getId(),
            $this->categoryParameterRepository->getCategoryParametersByCategorySortedByPosition($category),
        );
    }

    public function editParameterValue(int $parameterValueId, ParameterValueData $parameterValueData): ParameterValue
    {
        $parameterValue = $this->parameterRepository->getParameterValueById($parameterValueId);
        $parameterValue->edit($parameterValueData);

        $shouldManageFiles = $parameterValueData->colorIcon->uploadedFilenames
            || $parameterValueData->colorIcon->relations
            || $parameterValueData->colorIcon->currentFilenamesIndexedById
            || $parameterValueData->colorIcon->namesIndexedById;

        if ($shouldManageFiles) {
            $this->uploadedFileFacade->manageFiles($parameterValue, $parameterValueData->colorIcon);
        }

        if (count($parameterValueData->colorIcon->uploadedFilenames) === 0 && count($parameterValueData->colorIcon->relations) === 0 && $parameterValueData->colorIcon->filesToDelete) {
            $this->uploadedFileFacade->deleteRelationsByEntityAndUploadedFiles($parameterValue, $parameterValueData->colorIcon->filesToDelete, UploadedFileTypeConfig::DEFAULT_TYPE_NAME);
        }

        $this->em->flush();

        $this->dispatchParameterValueEvent([$parameterValue]);

        return $parameterValue;
    }

    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        return $this->parameterRepository->getParameterValueById($parameterValueId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByParameter(Parameter $parameter): array
    {
        return $this->parameterRepository->getParameterValuesByParameter($parameter);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionData[] $parameterValuesConversionDataIndexedByParameterValueId
     */
    public function updateParameterValuesByConversion(
        Parameter $parameter,
        array $parameterValuesConversionDataIndexedByParameterValueId,
    ): void {
        $newParameterValues = [];

        foreach ($parameterValuesConversionDataIndexedByParameterValueId as $parameterValueId => $parameterValueConversionData) {
            $parameterValue = $this->parameterRepository->getParameterValueById($parameterValueId);
            $parameterValueData = $this->parameterValueDataFactory->createFromParameterValue($parameterValue);

            $parameterValueData->text = $parameterValueConversionData->newValueText;
            $parameterValueData->numericValue = $parameterValueConversionData->newValueText;

            $newParameterValue = $this->parameterRepository->findOrCreateParameterValueByParameterValueData($parameterValueData);

            $this->parameterRepository->updateParameterValueInProductsByConversion($parameter, $parameterValue, $newParameterValue);
            $newParameterValues[] = $newParameterValue;
        }

        $this->dispatchParameterValueEvent($newParameterValues);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getSliderParametersWithoutTheirsNumericValueFilled(): array
    {
        return $this->parameterRepository->getSliderParametersWithoutTheirsNumericValueFilled();
    }

    public function getCountOfSliderParametersWithoutTheirsNumericValueFilled(): int
    {
        return $this->parameterRepository->getCountOfSliderParametersWithoutTheirsNumericValueFilled();
    }

    public function getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder(Parameter $parameter): int
    {
        return $this->parameterRepository->getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder($parameter);
    }

    /**
     * @param string[] $parameterUuids
     * @return array<string, int>
     */
    public function getParameterIdsIndexedByUuids(array $parameterUuids): array
    {
        return $this->parameterRepository->getParameterIdsIndexedByUuids($parameterUuids);
    }

    /**
     * @param string[] $parameterValueUuids
     * @return array<string, int>
     */
    public function getParameterValueIdsIndexedByUuids(array $parameterValueUuids): array
    {
        return $this->parameterRepository->getParameterValueIdsIndexedByUuids($parameterValueUuids);
    }

    public function getParameterValueIdByText(string $text, string $locale): int
    {
        try {
            return $this->parameterRepository->getParameterValueIdByText($text, $locale);
        } catch (NoResultException) {
            throw new ParameterValueNotFoundException();
        }
    }
}
