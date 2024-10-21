<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class FlagsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function flagsQuery(): array
    {
        return $this->flagFacade->getAllVisibleFlags($this->domain->getLocale());
    }
}
