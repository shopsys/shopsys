<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class InquiryFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly InquiryRepository $inquiryRepository,
        protected readonly InquiryFactory $inquiryFactory,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    public function getById(int $id): Inquiry
    {
        return $this->inquiryRepository->getById($id);
    }

    public function create(InquiryData $inquiryData): Inquiry
    {
        $inquiry = $this->inquiryFactory->create($inquiryData);

        $this->em->persist($inquiry);
        $this->em->flush();

        return $inquiry;
    }

    public function getInquiryListQueryBuilderByQuickSearchData(
        QuickSearchFormData $quickSearchData,
        string $locale,
    ): QueryBuilder {
        $queryBuilder = $this->inquiryRepository->getInquiriesQueryBuilder($locale);

        if ($quickSearchData->text !== null && $quickSearchData->text !== '') {
            $queryBuilder
                ->andWhere('(
                    NORMALIZED(i.productCatnum) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(p.catnum) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(pt.name) LIKE NORMALIZED(:text)
                    OR
                    i.companyNumber LIKE :text
                    OR
                    NORMALIZED(i.lastName) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(i.companyName) LIKE NORMALIZED(:text)
                    OR
                    NORMALIZED(i.email) LIKE NORMALIZED(:text)
                )');
            $querySearchText = $this->databaseSearchingHelper->getFullTextLikeSearchString($quickSearchData->text);
            $queryBuilder->setParameter('text', $querySearchText);
        }

        return $queryBuilder;
    }
}
