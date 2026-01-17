<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class TransportsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function transportsQuery(?string $cartUuid = null): array
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations();
        }

        $cart = $this->cartApiFacade->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations();
        }

        return $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations($cart);
    }
}
