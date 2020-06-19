<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Model\Category\CategoryParameter;
use App\Model\Product\Product;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoiceRepository as BaseParameterFilterChoiceRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Product\ProductRepository $productRepository)
 * @method \App\Model\Product\Parameter\ParameterValue[][] getParameterValuesIndexedByParameterIdOrderedByValueText(array $rows, string $locale)
 * @method \App\Model\Product\Parameter\ParameterValue[] getParameterValuesIndexedByIdOrderedByText(array $rows, string $locale)
 */
class ParameterFilterChoiceRepository extends BaseParameterFilterChoiceRepository
{
    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param \App\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    public function getParameterFilterChoicesInCategory($domainId, PricingGroup $pricingGroup, $locale, Category $category)
    {

        //sellable means product type none and variant
        $productsQueryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category
        );

        $productsQueryBuilder
            ->select('MIN(p), pp, pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'ppv.product = p')
            ->join(Parameter::class, 'pp', Join::WITH, 'pp = ppv.parameter')
            ->join(ParameterValue::class, 'pv', Join::WITH, 'pv = ppv.value AND pv.locale = :locale')
            ->join(CategoryParameter::class, 'cp', Join::WITH, 'cp.parameter = pp AND pcd.category = cp.category')
            ->groupBy('pp, pv')
            ->resetDQLPart('orderBy')
            ->setParameter('locale', $locale);

        $rows = $productsQueryBuilder->getQuery()->execute(null, GroupedScalarHydrator::HYDRATION_MODE);

        $visibleParametersIndexedById = $this->getVisibleParametersIndexedByIdOrderedByName($rows, $locale);
        $parameterValuesIndexedByParameterId = $this->getParameterValuesIndexedByParameterIdOrderedByValueText($rows, $locale);
        $parameterFilterChoices = [];

        foreach ($visibleParametersIndexedById as $parameterId => $parameter) {
            if (array_key_exists($parameterId, $parameterValuesIndexedByParameterId)) {
                $parameterFilterChoices[] = new ParameterFilterChoice(
                    $parameter,
                    $parameterValuesIndexedByParameterId[$parameterId]
                );
            }
        }

        return $parameterFilterChoices;
    }

    /**
     * @param array $rows
     * @param string $locale
     * @return \App\Model\Product\Parameter\Parameter[]
     */
    protected function getVisibleParametersIndexedByIdOrderedByName(array $rows, $locale): array
    {
        $parameterIds = [];
        foreach ($rows as $row) {
            $parameterIds[$row['pp']['id']] = $row['pp']['id'];
        }

        $parametersQueryBuilder = $this->em->createQueryBuilder()
            ->select('pp, pt')
            ->from(Parameter::class, 'pp')
            ->join('pp.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->where('pp.id IN (:parameterIds)')
            ->orderBy('pt.name', 'asc');
        $parametersQueryBuilder->setParameter('parameterIds', $parameterIds);
        $parametersQueryBuilder->setParameter('locale', $locale);
        $parameters = $parametersQueryBuilder->getQuery()->execute();

        $parametersIndexedById = [];
        foreach ($parameters as $parameter) {
            /* @var $parameter \App\Model\Product\Parameter\Parameter */
            $parametersIndexedById[$parameter->getId()] = $parameter;
        }

        return $parametersIndexedById;
    }
}
