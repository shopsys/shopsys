<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Flag;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Product\Flag\Flag;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagResolverMap extends ResolverMap
{
    /**
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(private FriendlyUrlFacade $friendlyUrlFacade, private Domain $domain)
    {
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
            ],
        ];
    }

    /**
     * @param \App\Model\Product\Flag\Flag $flag
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
