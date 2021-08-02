<?php

declare(strict_types=1);

namespace App\Model\Breadcrumb;

use Shopsys\FrameworkBundle\Model\Breadcrumb\ErrorPageBreadcrumbGenerator as BaseErrorPageBreadcrumbGenerator;

class ErrorPageBreadcrumbGenerator extends BaseErrorPageBreadcrumbGenerator
{
    /**
     * @return string
     */
    protected function getTranslatedBreadcrumbForNotFoundPage(): string
    {
        return t('Page not found');
    }

    /**
     * @return string
     */
    protected function getTranslatedBreadcrumbForErrorPage(): string
    {
        return t('Oops! Error occurred');
    }
}
