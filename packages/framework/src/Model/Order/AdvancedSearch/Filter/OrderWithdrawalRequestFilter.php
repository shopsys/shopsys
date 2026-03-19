<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter;
use Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormTypeInterface;

class OrderWithdrawalRequestFilter extends AbstractAdvancedSearchFilter
{
    public const string NAME = 'orderWithdrawalRequest';

    public function __construct(
        DatabaseSearchingHelper $databaseSearchingHelper,
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct($databaseSearchingHelper);
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
    public function getLabel(): string
    {
        return t('Withdrawal Request');
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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getEntityType(): string
    {
        return OrderAdvancedSearchFacade::getEntityType();
    }
}
