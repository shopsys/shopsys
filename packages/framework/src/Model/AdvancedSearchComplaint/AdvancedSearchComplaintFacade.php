<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory;
use Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\ComplaintNumberFilter;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class AdvancedSearchComplaintFacade
{
    public const string RULES_FORM_NAME = 'as';

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\ComplaintAdvancedSearchFormFactory $complaintAdvancedSearchFormFactory
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender
     * @param \Shopsys\FrameworkBundle\Model\AdvancedSearch\RuleFormViewDataFactory $ruleFormViewDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintRepository $complaintRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly ComplaintAdvancedSearchFormFactory $complaintAdvancedSearchFormFactory,
        protected readonly AdvancedSearchQueryBuilderExtender $advancedSearchQueryBuilderExtender,
        protected readonly RuleFormViewDataFactory $ruleFormViewDataFactory,
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createAdvancedSearchComplaintForm(Request $request): FormInterface
    {
        $rawRulesData = $request->get(static::RULES_FORM_NAME);
        $rulesData = is_array($rawRulesData) ? $rawRulesData : [];
        $rulesFormData = $this->ruleFormViewDataFactory->createFromRequestData(
            ComplaintNumberFilter::NAME,
            $rulesData,
        );

        return $this->complaintAdvancedSearchFormFactory->createRulesForm(
            static::RULES_FORM_NAME,
            $rulesFormData,
        );
    }

    /**
     * @param string $filterName
     * @param string|int $index
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createRuleForm(string $filterName, string|int $index): FormInterface
    {
        $rulesData = [
            $index => $this->ruleFormViewDataFactory->createDefault($filterName),
        ];

        return $this->complaintAdvancedSearchFormFactory->createRulesForm(static::RULES_FORM_NAME, $rulesData);
    }

    /**
     * @param array $advancedSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByAdvancedSearchData(array $advancedSearchData): QueryBuilder
    {
        $queryBuilder = $this->complaintRepository->getComplaintsQueryBuilder($this->localization->getAdminLocale());
        $this->advancedSearchQueryBuilderExtender->extendByAdvancedSearchData($queryBuilder, $advancedSearchData);

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getComplaintListQueryBuilderByQuickSearchData(
        QuickSearchFormData $quickSearchData,
    ): QueryBuilder {
        $queryBuilder = $this->complaintRepository->getComplaintsQueryBuilder($this->localization->getAdminLocale());

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('
                    (
                        cmp.number LIKE :text
                        OR
                        o.number LIKE :text
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
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function isAdvancedSearchComplaintFormSubmitted(Request $request): bool
    {
        $rulesData = $request->get(static::RULES_FORM_NAME);

        return $rulesData !== null;
    }
}
