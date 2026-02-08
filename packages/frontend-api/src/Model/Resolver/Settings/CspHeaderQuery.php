<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CspHeaderQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Setting $setting,
    ) {
    }

    public function cspHeaderQuery(): string
    {
        return $this->setting->get(Setting::CSP_HEADER);
    }
}
