<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Doctrine\OrderByCollationHelper;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameter;
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
    public function getParameterFilterChoicesInCategory(
        $domainId,
        PricingGroup $pricingGroup,
        $locale,
        Category $category,
    ) {
        // it must contain variants + main variants
        $productsQueryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
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

        $visibleParametersIndexedById = $this->getVisibleParametersIndexedByIdOrderedByParameterPositionInCategory($rows, $locale, $category);
        $parameterValuesIndexedByParameterId = $this->getParameterValuesIndexedByParameterIdOrderedByValueText($rows, $locale);
        $parameterFilterChoices = [];

        foreach ($visibleParametersIndexedById as $parameterId => $parameter) {
            if (array_key_exists($parameterId, $parameterValuesIndexedByParameterId)) {
                $parameterFilterChoices[] = new ParameterFilterChoice(
                    $parameter,
                    $parameterValuesIndexedByParameterId[$parameterId],
                );
            }
        }

        return $parameterFilterChoices;
    }

    /**
     * @param array $rows
     * @param string $locale
     * @param \App\Model\Category\Category $category
     * @return array
     */
    protected function getVisibleParametersIndexedByIdOrderedByParameterPositionInCategory(
        array $rows,
        string $locale,
        Category $category,
    ): array {
        $parameterIds = [];

        foreach ($rows as $row) {
            $parameterIds[$row['pp']['id']] = $row['pp']['id'];
        }

        $parametersQueryBuilder = $this->em->createQueryBuilder()
            ->select('pp, pt')
            ->from(Parameter::class, 'pp')
            ->join('pp.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->join(CategoryParameter::class, 'cp', Join::WITH, 'cp.parameter = pp AND cp.category = :category')
            ->where('pp.id IN (:parameterIds)')
            ->orderBy('cp.position', 'asc');
        $parametersQueryBuilder->setParameter('parameterIds', $parameterIds);
        $parametersQueryBuilder->setParameter('locale', $locale);
        $parametersQueryBuilder->setParameter('category', $category);
        $parameters = $parametersQueryBuilder->getQuery()->execute();

        $parametersIndexedById = [];

        foreach ($parameters as $parameter) {
            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parametersIndexedById[$parameter->getId()] = $parameter;
        }

        return $parametersIndexedById;
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
            ->andWhere('pp.visible = true')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('pt.name', $locale), 'asc');
        $parametersQueryBuilder->setParameter('parameterIds', $parameterIds);
        $parametersQueryBuilder->setParameter('locale', $locale);
        $parameters = $parametersQueryBuilder->getQuery()->execute();

        $parametersIndexedById = [];
        /** @var \App\Model\Product\Parameter\Parameter $parameter */
        foreach ($parameters as $parameter) {
            $parametersIndexedById[$parameter->getId()] = $parameter;
        }

        return $parametersIndexedById;
    }

    /**
     * @param array $rows
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue[]
     */
    protected function getParameterValuesIndexedByIdOrderedByText(array $rows, $locale): array
    {
        $valueIds = [];

        foreach ($rows as $row) {
            $valueId = $row['pv']['id'];
            $valueIds[$valueId] = $valueId;
        }

        $valuesQueryBuilder = $this->em->createQueryBuilder()
            ->select('pv')
            ->from(ParameterValue::class, 'pv')
            ->where('pv.id IN (:valueIds)')
            ->andWhere('pv.locale = :locale')
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('pv.text', $locale), 'asc');
        $valuesQueryBuilder->setParameter('valueIds', $valueIds);
        $valuesQueryBuilder->setParameter('locale', $locale);
        $values = $valuesQueryBuilder->getQuery()->execute();

        $valuesIndexedById = [];
        /** @var \App\Model\Product\Parameter\ParameterValue $value */
        foreach ($values as $value) {
            $valuesIndexedById[$value->getId()] = $value;
        }

        return $valuesIndexedById;
    }
}
