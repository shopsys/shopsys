<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ParameterFilterChoiceRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    public function getParameterFilterChoicesInCategory(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        Category $category,
    ): array {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        $productsQueryBuilder
            ->select('MIN(p), pp, pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'ppv.product = p')
            ->join(Parameter::class, 'pp', Join::WITH, 'pp = ppv.parameter')
            ->join(ParameterValue::class, 'pv', Join::WITH, 'pv = ppv.value AND pv.locale = :locale')
            ->groupBy('pp, pv')
            ->resetDQLPart('orderBy')
            ->setParameter('locale', $locale);

        $rows = $productsQueryBuilder->getQuery()->getResult(GroupedScalarHydrator::HYDRATION_MODE);

        $visibleParametersIndexedById = $this->getVisibleParametersIndexedByIdOrderedByName($rows, $locale);
        $parameterValuesIndexedByParameterId = $this->getParameterValuesIndexedByParameterIdOrderedByValueText(
            $rows,
            $locale,
        );
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    protected function getVisibleParametersIndexedByIdOrderedByName(array $rows, string $locale): array
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
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('pt.name', $locale), 'asc');
        $parametersQueryBuilder->setParameter('parameterIds', $parameterIds);
        $parametersQueryBuilder->setParameter('locale', $locale);
        $parameters = $parametersQueryBuilder->getQuery()->getResult();

        $parametersIndexedById = [];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter */
        foreach ($parameters as $parameter) {
            $parametersIndexedById[$parameter->getId()] = $parameter;
        }

        return $parametersIndexedById;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[][]
     */
    protected function getParameterValuesIndexedByParameterIdOrderedByValueText(array $rows, string $locale): array
    {
        $parameterIdsByValueId = [];

        foreach ($rows as $row) {
            $valueId = $row['pv']['id'];
            $parameterId = $row['pp']['id'];
            $parameterIdsByValueId[$valueId][] = $parameterId;
        }

        $valuesIndexedById = $this->getParameterValuesIndexedByIdOrderedByText($rows, $locale);

        $valuesIndexedByParameterId = [];

        foreach ($valuesIndexedById as $valueId => $value) {
            foreach ($parameterIdsByValueId[$valueId] as $parameterId) {
                $valuesIndexedByParameterId[$parameterId][] = $value;
            }
        }

        return $valuesIndexedByParameterId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    protected function getParameterValuesIndexedByIdOrderedByText(array $rows, string $locale): array
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
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('pv.text', $locale), 'asc');
        $valuesQueryBuilder->setParameter('valueIds', $valueIds);
        $valuesQueryBuilder->setParameter('locale', $locale);
        $values = $valuesQueryBuilder->getQuery()->getResult();

        $valuesIndexedById = [];

        foreach ($values as $value) {
            $valuesIndexedById[$value->getId()] = $value;
        }

        return $valuesIndexedById;
    }
}
