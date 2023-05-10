<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Flag;

use App\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class FlagsQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly FlagFacade $flagFacade,
        private readonly Domain $domain
    ) {
    }

    /**
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function flagsQuery(): array
    {
        return $this->flagFacade->getAllVisibleFlags($this->domain->getLocale());
    }
}
