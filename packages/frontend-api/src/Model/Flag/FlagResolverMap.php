<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Flag;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class FlagResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
    }

    /**
     * @return array<string, array<string, callable>>
     */
    protected function map(): array
    {
        return [
            'Flag' => [
                'name' => function (Flag $flag) {
                    return $flag->getName($this->domain->getLocale()) ?? '';
                },
                'slug' => function (Flag $flag) {
                    return $this->getSlug($flag);
                },
                'hreflangLinks' => function (Flag $flag) {
                    return $this->hreflangLinksFacade->getForFlag($flag, $this->domain->getId());
                },
            ],
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return string
     */
    private function getSlug(Flag $flag): string
    {
        $friendlyUrlSlug = $this->friendlyUrlFacade->getMainFriendlyUrlSlug(
            $this->domain->getId(),
            'front_flag_detail',
            $flag->getId(),
        );

        return '/' . $friendlyUrlSlug;
    }
}
