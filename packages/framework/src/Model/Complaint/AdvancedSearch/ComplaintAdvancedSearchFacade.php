<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFormFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintNumberFilter;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ComplaintAdvancedSearchFacade extends AbstractAdvancedSearchFacade
{
    public function __construct(
        AdvancedSearchFormFactory $advancedSearchFormFactory,
        RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly Localization $localization,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
        parent::__construct($advancedSearchFormFactory, $ruleFormViewDataFactory);
    }

    /**
     * @param array<\Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchRuleData|null> $advancedSearchData
     */
    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData): QueryBuilder
    {
        $queryBuilder = $this->complaintRepository->getComplaintsQueryBuilder($this->localization->getCurrentLocaleForTranslatableEntities());
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData, static::getEntityType());

        return $queryBuilder;
    }

    public function getComplaintListQueryBuilderByQuickSearchData(
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $queryBuilder = $this->complaintRepository->getComplaintsQueryBuilder($this->localization->getCurrentLocaleForTranslatableEntities());

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $uuidCondition = '';

            if (Uuid::isValid($quickSearchData->text)) {
                $uuidCondition = 'cmp.uuid = :exactText OR ';
                $queryBuilder->setParameter('exactText', $quickSearchData->text);
            }

            $queryBuilder
                ->andWhere('
                    (
                        ' . $uuidCondition . '
                        cmp.number LIKE :text
                        OR
                        NORMALIZED(cmp.email) LIKE NORMALIZED(:text)
                        OR
                        o.number LIKE :text
                        OR
                        NORMALIZED(cmp.manualDocumentNumber) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(cmp.deliveryLastName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(cmp.deliveryCompanyName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(o.lastName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(o.email) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(ba.companyName) LIKE NORMALIZED(:text)
                        OR
                        NORMALIZED(cu.lastName) LIKE NORMALIZED(:text)
                    )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    #[Override]
    public static function getEntityType(): string
    {
        return 'complaint';
    }

    #[Override]
    protected function getDefaultFilterName(): string
    {
        return ComplaintNumberFilter::NAME;
    }
}
