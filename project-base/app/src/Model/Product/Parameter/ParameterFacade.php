<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade as BaseParameterFacade;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactoryInterface $parameterFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher, \Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository $categoryParameterRepository, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactory $parameterValueFactory, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher, \Shopsys\FrameworkBundle\Model\CategorySeo\DeleteReadyCategorySeoMixFacade $deleteReadyCategorySeoMixFacade)
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter getByUuid(string $uuid)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\Parameter[] getAllWithTranslations(string $locale)
 * @method \App\Model\Product\Parameter\Parameter create(\App\Model\Product\Parameter\ParameterData $parameterData)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 * @method bool existsParameterByName(string $name, string $locale, \App\Model\Product\Parameter\Parameter|null $excludeParameter = null)
 * @method \App\Model\Product\Parameter\Parameter edit(int $parameterId, \App\Model\Product\Parameter\ParameterData $parameterData)
 * @method dispatchParameterEvent(\App\Model\Product\Parameter\Parameter $parameter, string $eventType)
 * @method \App\Model\Product\Parameter\Parameter[] getParametersByUuids(string[] $uuids)
 * @method int[] getParametersIdsSortedByPositionFilteredByCategory(\App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] getParameterValuesByParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method updateParameterValuesByConversion(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionData[] $parameterValuesConversionDataIndexedByParameterValueId)
 * @method \App\Model\Product\Parameter\Parameter[] getSliderParametersWithoutTheirsNumericValueFilled()
 * @method int getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder(\App\Model\Product\Parameter\Parameter $parameter)
 */
class ParameterFacade extends BaseParameterFacade
{
}
