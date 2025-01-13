<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade as BaseParameterFacade;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFactory $parameterFactory, \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher, \Shopsys\FrameworkBundle\Model\Category\CategoryParameterRepository $categoryParameterRepository, \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactory $parameterValueFactory, \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher, \Shopsys\FrameworkBundle\Model\CategorySeo\DeleteReadyCategorySeoMixFacade $deleteReadyCategorySeoMixFacade)
 * @method int[] getParametersIdsSortedByPositionFilteredByCategory(\App\Model\Category\Category $category)
 */
class ParameterFacade extends BaseParameterFacade
{
}
