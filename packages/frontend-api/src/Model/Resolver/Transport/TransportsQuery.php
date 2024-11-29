<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeProvider;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class TransportsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportTypeProvider $transportTypeProvider
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly TransportTypeProvider $transportTypeProvider,
    ) {
    }

    /**
     * @param bool $displayInCartOnly
     * @param string|null $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function transportsQuery(bool $displayInCartOnly, ?string $cartUuid = null): array
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($customerUser === null && $cartUuid === null) {
            return $this->filterByDisplayInCartOnly(
                $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations(),
                $displayInCartOnly,
            );
        }

        $cart = $this->cartApiFacade->findCart($customerUser, $cartUuid);

        if ($cart === null) {
            return $this->filterByDisplayInCartOnly(
                $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations(),
                $displayInCartOnly,
            );
        }

        return $this->filterByDisplayInCartOnly(
            $this->transportFacade->getVisibleOnCurrentDomainWithEagerLoadedDomainsAndTranslations($cart),
            $displayInCartOnly,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param bool $displayInCartOnly
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function transportsOfPaymentQuery(Payment $payment, bool $displayInCartOnly): array
    {
        return $this->filterByDisplayInCartOnly($this->transportFacade->getVisibleOnCurrentDomain([$payment]), $displayInCartOnly);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param bool $displayInCartOnly
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    protected function filterByDisplayInCartOnly(array $transports, bool $displayInCartOnly): array
    {
        if ($displayInCartOnly === false) {
            return $transports;
        }

        $displayInCartOnlyTransportTypes = $this->transportTypeProvider->getAllEnabledInCartIndexedByTranslations();

        $displayInCartOnlyTransports = [];

        foreach ($transports as $transport) {
            if (in_array($transport->getType(), $displayInCartOnlyTransportTypes, true)) {
                $displayInCartOnlyTransports[] = $transport;
            }
        }

        return $displayInCartOnlyTransports;
    }
}
