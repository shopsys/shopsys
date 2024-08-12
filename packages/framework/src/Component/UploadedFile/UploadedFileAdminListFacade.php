<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class UploadedFileAdminListFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileAdminListRepository $uploadedFileAdminListRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly UploadedFileAdminListRepository $uploadedFileAdminListRepository,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUploadedFileListQueryBuilder(): QueryBuilder
    {
        $locale = $this->localization->getAdminLocale();

        return $this->uploadedFileAdminListRepository->getUploadedFileListQueryBuilder($locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): QueryBuilder
    {
        $queryBuilder = $this->getUploadedFileListQueryBuilder();
        $this->uploadedFileAdminListRepository->extendQueryBuilderByQuickSearchData($queryBuilder, $quickSearchData);

        return $queryBuilder;
    }
}
