<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class PersonalDataBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        if (in_array($routeName, $this->getPersonalDataRouteNames(), true)) {
            $breadcrumbItem = new BreadcrumbItem(t('Personal information overview', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN));
        } else {
            $breadcrumbItem = new BreadcrumbItem(t('Personal information export', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN));
        }

        return [$breadcrumbItem];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRouteNames()
    {
        return [
            'front_personal_data',
            'front_personal_data_access',
            'front_personal_data_export',
            'front_personal_data_access_export',
        ];
    }

    /**
     * @return array
     */
    protected function getPersonalDataRouteNames()
    {
        return [
            'front_personal_data',
            'front_personal_data_access',
        ];
    }
}
