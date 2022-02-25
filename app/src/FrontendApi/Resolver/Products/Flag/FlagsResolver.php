<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Flag;

use App\Model\Product\Flag\FlagFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class FlagsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Model\Product\Flag\FlagFacade
     */
    private FlagFacade $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(FlagFacade $flagFacade, Domain $domain)
    {
        $this->flagFacade = $flagFacade;
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function resolver(): array
    {
        return $this->flagFacade->getAllVisibleFlags($this->domain->getLocale());
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'flags',
        ];
    }
}
