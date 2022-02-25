<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Transport;

use App\FrontendApi\Model\Cart\CartFacade;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Transport\TransportsResolver as BaseTransportsResolver;

/**
 * @property \App\Model\Transport\TransportFacade $transportFacade
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 */
class TransportsResolver extends BaseTransportsResolver
{
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Payment\PaymentFacade $paymentFacade
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        TransportFacade $transportFacade,
        PaymentFacade $paymentFacade,
        CartFacade $cartFacade,
        CurrentCustomerUser $currentCustomerUser
    ) {
        parent::__construct($transportFacade, $paymentFacade);

        $this->cartFacade = $cartFacade;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param string|null $cartUuid
     * @return \App\Model\Transport\Transport[]
     */
    public function resolve(?string $cartUuid = null): array
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
