<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormTypeInterface;

class OrderWithdrawalRequestFilter implements AdvancedSearchFilterInterface
{
    public const string NAME = 'orderWithdrawalRequest';

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [
            self::OPERATOR_EXISTS,
            self::OPERATOR_DOES_NOT_EXIST,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): FormTypeInterface|string
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, array $rulesData): void
    {
        foreach ($rulesData as $ruleData) {
            $subQuery = $this->em->createQueryBuilder()
                ->select('1')
                ->from(WithdrawalRequest::class, 'wr')
                ->where('wr.order = o');

            if ($ruleData->operator === self::OPERATOR_EXISTS) {
                $queryBuilder->andWhere($queryBuilder->expr()->exists($subQuery->getDQL()));
            }

            if ($ruleData->operator === self::OPERATOR_DOES_NOT_EXIST) {
                $queryBuilder->andWhere($queryBuilder->expr()->not($queryBuilder->expr()->exists($subQuery->getDQL())));
            }
        }
    }
}
