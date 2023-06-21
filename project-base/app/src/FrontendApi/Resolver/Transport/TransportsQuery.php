<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Transport;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsQuery as BaseTransportsQuery;

/**
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 */
class TransportsQuery extends BaseTransportsQuery
{
    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        private readonly CartFacade $cartFacade,
        private readonly CurrentCustomerUser $currentCustomerUser,
    ) {
        parent::__construct($transportFacade, $paymentFacade);
    }

    /**
     * @param string|null $cartUuid
     * @return \App\Model\Transport\Transport[]
     */
    public function transportsQuery(?string $cartUuid = null): array
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations();
        }

        $cart = $this->cartFacade->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations();
        }

        return $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations($cart->getTotalWeight());
    }
}
