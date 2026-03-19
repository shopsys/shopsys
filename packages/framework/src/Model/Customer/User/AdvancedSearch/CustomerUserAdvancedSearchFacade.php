<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\AdvancedSearch\Filter\CustomerUserFirstNameFilter;
use Shopsys\FrameworkBundle\Model\Customer\User\Listing\CustomerUserListAdminFacade;

class CustomerUserAdvancedSearchFacade extends AbstractAdvancedSearchFacade
{
    public function __construct(
        AdvancedSearchFormFactory $advancedSearchFormFactory,
        RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly CustomerUserListAdminFacade $customerUserListAdminFacade,
    ) {
        parent::__construct($advancedSearchFormFactory, $ruleFormViewDataFactory);
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData|null> $advancedSearchData
     */
    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData, int $domainId): QueryBuilder
    {
        $queryBuilder = $this->customerUserListAdminFacade->getCustomerUserListQueryBuilder($domainId);
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData, static::getEntityType());

        return $queryBuilder;
    }

    #[Override]
    public static function getEntityType(): string
    {
        return 'customerUser';
    }

    #[Override]
    protected function getDefaultFilterName(): string
    {
        return CustomerUserFirstNameFilter::NAME;
    }
}
