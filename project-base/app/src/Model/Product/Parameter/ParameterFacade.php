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
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @property \App\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
 * @method int[] getParametersIdsSortedByPositionFilteredByCategory(\App\Model\Category\Category $category)
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
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
        ProductRecalculationDispatcher $productRecalculationDispatcher,
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
            $productRecalculationDispatcher,
        );
    }

    /**
     * @param int $parameterId
     */
    public function deleteById($parameterId): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter */
        $parameter = $this->parameterRepository->getById($parameterId);
        $this->readyCategorySeoMixFacade->deleteAllWithParameter($parameter);

        parent::deleteById($parameterId);
    }
}
