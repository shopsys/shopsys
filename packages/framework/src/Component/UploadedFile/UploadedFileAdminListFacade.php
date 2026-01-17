<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class UploadedFileAdminListFacade
{
    public function __construct(
        protected readonly UploadedFileAdminListRepository $uploadedFileAdminListRepository,
        protected readonly Localization $localization,
    ) {
    }

    public function getUploadedFileListQueryBuilder(): QueryBuilder
    {
        $locale = $this->localization->getCurrentLocaleForTranslatableEntities();

        return $this->uploadedFileAdminListRepository->getUploadedFileListQueryBuilder($locale);
    }

    public function getQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): QueryBuilder
    {
        $queryBuilder = $this->getUploadedFileListQueryBuilder();
        $this->uploadedFileAdminListRepository->extendQueryBuilderByQuickSearchData($queryBuilder, $quickSearchData);

        return $queryBuilder;
    }
}
