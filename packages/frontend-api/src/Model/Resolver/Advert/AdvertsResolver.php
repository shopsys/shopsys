<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade;

class AdvertsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade
     */
    protected $advertFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(AdvertFacade $advertFacade, Domain $domain)
    {
        $this->advertFacade = $advertFacade;
        $this->domain = $domain;
    }

    /**
     * @param string|null $positionName
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function resolve(?string $positionName = null): array
    {
        $domainId = $this->domain->getId();
        if ($positionName === null) {
            return $this->advertFacade->getVisibleAdvertsByDomainId($domainId);
        }
        return $this->advertFacade->getVisibleAdvertsByDomainIdAndPositionName($domainId, $positionName);
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'adverts',
        ];
    }
}
