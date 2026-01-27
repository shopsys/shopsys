<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch;

use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\ComplaintNumberFilter;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ComplaintAdvancedSearchFacade
{
    public const string RULES_FORM_NAME = 'as';

    public function __construct(
        protected readonly ComplaintAdvancedSearchFormFactory $complaintAdvancedSearchFormFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly Localization $localization,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    public function createAdvancedSearchComplaintForm(Request $request): FormInterface
    {
        $rulesData = $request->query->all(static::RULES_FORM_NAME);
        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(
            ComplaintNumberFilter::NAME,
            $rulesData,
        );

        return $this->complaintAdvancedSearchFormFactory->createRulesForm(
            static::RULES_FORM_NAME,
            $rulesFormData,
        );
    }

    public function createRuleForm(string $filterName, string $index): FormInterface
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->complaintAdvancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }

    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData): QueryBuilder
    {
        $queryBuilder = $this->complaintRepository->getComplaintsQueryBuilder($this->localization->getCurrentLocaleForTranslatableEntities());
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData);

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

    public function isAdvancedSearchComplaintFormSubmitted(Request $request): bool
    {
        return $request->query->has(static::RULES_FORM_NAME);
    }
}
