<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Component\UploadedFile\UploadedFileFacade;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade as BaseParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter getByUuid(string $uuid)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\Parameter create(\App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 * @method \App\Model\Product\Parameter\Parameter edit(int $parameterId, \App\Model\Product\Parameter\ParameterData $parameterData)
 * @method dispatchParameterEvent(\App\Model\Product\Parameter\Parameter $parameter, string $eventType)
 * @method \App\Model\Product\Parameter\Parameter[] getParametersByUuids(string[] $uuids)
 * @method int[] getParametersIdsSortedByPositionFilteredByCategory(\App\Model\Category\Category $category)
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] getParameterValuesByParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method updateParameterValuesByConversion(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionData[] $parameterValuesConversionDataIndexedByParameterValueId)
 * @method \App\Model\Product\Parameter\Parameter[] getSliderParametersWithoutTheirsNumericValueFilled()
 * @method int getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder(\App\Model\Product\Parameter\Parameter $parameter)
 */
class ParameterFacade extends BaseParameterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactory $parameterFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository $categoryParameterRepository
     * @param \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactory $parameterValueFactory
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ParameterFactoryInterface $parameterFactory,
        EventDispatcherInterface $eventDispatcher,
        CategoryParameterRepository $categoryParameterRepository,
        UploadedFileFacade $uploadedFileFacade,
        ParameterValueDataFactory $parameterValueDataFactory,
        ParameterValueFactory $parameterValueFactory,
        private readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
        parent::__construct(
            $em,
            $parameterRepository,
            $parameterFactory,
            $eventDispatcher,
            $categoryParameterRepository,
            $uploadedFileFacade,
            $parameterValueDataFactory,
            $parameterValueFactory,
        );
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
     * @param int $parameterId
     */
    public function deleteById($parameterId): void
    {
        /** @var \App\Model\Product\Parameter\Parameter $parameter */
        $parameter = $this->parameterRepository->getById($parameterId);
        $this->readyCategorySeoMixFacade->deleteAllWithParameter($parameter);

        parent::deleteById($parameterId);
    }
}
