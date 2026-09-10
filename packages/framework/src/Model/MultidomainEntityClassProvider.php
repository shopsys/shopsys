<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\MultidomainEntityClassProviderInterface;
use Shopsys\FrameworkBundle\Component\Setting\SettingValue;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDomain;
use Shopsys\FrameworkBundle\Model\Category\CategoryDomain;
use Shopsys\FrameworkBundle\Model\Country\CountryDomain;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDomain;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageDomain;
use Shopsys\FrameworkBundle\Model\Stock\StockDomain;
use Shopsys\FrameworkBundle\Model\Transport\TransportDomain;
use Shopsys\FrameworkBundle\Model\Transport\TransportPrice;

class MultidomainEntityClassProvider implements MultidomainEntityClassProviderInterface
{
    /**
     * @return string[]
     */
    #[Override]
    public function getIgnoredMultidomainEntitiesNames(): array
    {
        return [
            SettingValue::class,
            ProductVisibility::class,
        ];
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getManualMultidomainEntitiesNames(): array
    {
        return [
            AdditionalServiceDomain::class,
            BrandDomain::class,
            CategoryDomain::class,
            MailTemplate::class,
            PaymentDomain::class,
            ProductDomain::class,
            TransportDomain::class,
            CountryDomain::class,
            SeoPageDomain::class,
            StockDomain::class,
            TransportPrice::class,
        ];
    }
}
