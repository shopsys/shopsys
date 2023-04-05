<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class SeoSettingsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly SeoSettingFacade $seoSettingFacade,
    ) {
    }

    /**
     * @return array{robots: string[]}
     */
    public function seoSettingsQuery(): array
    {
        $robotsContent = $this->seoSettingFacade->getRobotsContent($this->domain->getId());

        return [
            'robots' => $robotsContent !== null ? preg_split('/\r\n|[\r\n]/', $robotsContent) : [],
        ];
    }
}
