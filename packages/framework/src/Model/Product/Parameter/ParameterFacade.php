<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParameterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactory $parameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly ParameterFactoryInterface $parameterFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CategoryParameterRepository $categoryParameterRepository,
        protected readonly UploadedFileFacade $uploadedFileFacade,
        protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        protected readonly ParameterValueFactory $parameterValueFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
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

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getByUuid(string $uuid): Parameter
    {
        return $this->parameterRepository->getByUuid($uuid);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
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
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAllWithTranslations(string $locale): array
    {
        return $this->parameterRepository->getAllWithTranslations($locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
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

    /**
     * @param int $parameterId
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
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

        $this->em->remove($parameter);

        $this->dispatchParameterEvent($parameter, ParameterEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @param string $valueText
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueByValueTextAndLocale(string $valueText, string $locale): ParameterValue
    {
        return $this->parameterRepository->getParameterValueByValueTextAndLocale($valueText, $locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterEvent class
     */
    protected function dispatchParameterEvent(Parameter $parameter, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new ParameterEvent($parameter), $eventType);
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

            uasort($parameterValues, static function (ParameterValue $first, ParameterValue $second) {
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
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return int[]
     */
    public function getParametersIdsSortedByPositionFilteredByCategory(Category $category): array
    {
        return array_map(
            static fn ($categoryParameter) => $categoryParameter->getParameter()->getId(),
            $this->categoryParameterRepository->getCategoryParametersByCategorySortedByPosition($category),
        );
    }

    /**
     * @param int $parameterValueId
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function editParameterValue(int $parameterValueId, ParameterValueData $parameterValueData): ParameterValue
    {
        $parameterValue = $this->parameterRepository->getParameterValueById($parameterValueId);
        $parameterValue->edit($parameterValueData);

        if ($parameterValueData->colourIcon->uploadedFilenames) {
            $this->uploadedFileFacade->manageFiles($parameterValue, $parameterValueData->colourIcon);
        }

        if (count($parameterValueData->colourIcon->uploadedFilenames) === 0 && $parameterValueData->colourIcon->filesToDelete) {
            $this->uploadedFileFacade->deleteFiles($parameterValue, $parameterValueData->colourIcon->filesToDelete, UploadedFileTypeConfig::DEFAULT_TYPE_NAME);
        }

        $this->em->flush();

        return $parameterValue;
    }

    /**
     * @param int $parameterValueId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        return $this->parameterRepository->getParameterValueById($parameterValueId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByParameter(Parameter $parameter): array
    {
        return $this->parameterRepository->getParameterValuesByParameter($parameter);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
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

            $parameterValueData->uuid = null;
            $parameterValueData->text = $parameterValueConversionData->newValueText;
            $parameterValueData->numericValue = $parameterValueConversionData->newValueText;

            $newParameterValue = $this->parameterValueFactory->create($parameterValueData);
            $this->em->persist($newParameterValue);
            $this->em->flush();

            $this->parameterRepository->updateParameterValueInProductsByConversion($parameter, $parameterValue, $newParameterValue);
            $newParameterValues[] = $newParameterValue;
        }

        $productsChangedByConversion = $this->parameterRepository->getProductsByParameterValues($newParameterValues);

        $this->productRecalculationDispatcher->dispatchProducts($productsChangedByConversion, exportScopes: [ProductExportScopeConfig::SCOPE_PARAMETERS]);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getSliderParametersWithoutTheirsNumericValueFilled(): array
    {
        return $this->parameterRepository->getSliderParametersWithoutTheirsNumericValueFilled();
    }

    /**
     * @return int
     */
    public function getCountOfSliderParametersWithoutTheirsNumericValueFilled(): int
    {
        return $this->parameterRepository->getCountOfSliderParametersWithoutTheirsNumericValueFilled();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return int
     */
    public function getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder(Parameter $parameter): int
    {
        return $this->parameterRepository->getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder($parameter);
    }
}
