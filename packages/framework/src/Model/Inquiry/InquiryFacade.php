<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class InquiryFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryRepository $inquiryRepository
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryFactory $inquiryFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly InquiryRepository $inquiryRepository,
        protected readonly InquiryFactory $inquiryFactory,
    ) {
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry
     */
    public function getById(int $id): Inquiry
    {
        return $this->inquiryRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryData $inquiryData
     * @return \Shopsys\FrameworkBundle\Model\Inquiry\Inquiry
     */
    public function create(InquiryData $inquiryData): Inquiry
    {
        $inquiry = $this->inquiryFactory->create($inquiryData);

        $this->em->persist($inquiry);
        $this->em->flush();

        return $inquiry;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getInquiryListQueryBuilderByQuickSearchData(
        QuickSearchFormData $quickSearchData,
        string $locale,
    ): QueryBuilder {
        $queryBuilder = $this->inquiryRepository->getInquiriesQueryBuilder($locale);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('(
                    i.companyNumber LIKE :text
                    OR
                    NORMALIZED(i.lastName) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(i.companyName) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(i.email) LIKE NORMALIZED(:text)
                )');
            $querySearchText = DatabaseSearching::getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }
}
