<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Transport\Exception\TransportNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Store\StoreConnection;
use Shopsys\FrontendApiBundle\Model\Store\StoreConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Transport\ProductDeliveryOption;

class ProductDeliveryOptionsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly TransportFacade $transportFacade,
        protected readonly TransportPriceProvider $transportPriceProvider,
        protected readonly TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Domain $domain,
        protected readonly StoreConnectionFactory $storeConnectionFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Transport\ProductDeliveryOption[]
     */
    public function productDeliveryOptionsQuery(string $productUuid): array
    {
        $product = $this->productFacade->getSellableByUuid(
            $productUuid,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        $transports = $this->transportFacade->getUsableForSingleProductOnCurrentDomainWithEagerLoadedDomainsAndTranslations(
            $product,
        );

        $productDeliveryOptions = [];

        foreach ($transports as $transport) {
            try {
                $productDeliveryOptions[] = $this->createProductDeliveryOption($transport, $product);
            } catch (TransportPriceNotFoundException) {
                continue;
            }
        }

        return $productDeliveryOptions;
    }

    public function productDeliveryStoresQuery(
        string $productUuid,
        string $transportUuid,
        Argument $argument,
    ): StoreConnection {
        $product = $this->productFacade->getSellableByUuid(
            $productUuid,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );
        $transport = $this->getUsablePersonalPickupTransportForProduct($transportUuid, $product);

        return $this->storeConnectionFactory->createProductDeliveryStoreConnection($argument, $transport, $product);
    }

    protected function createProductDeliveryOption(Transport $transport, Product $product): ProductDeliveryOption
    {
        return new ProductDeliveryOption(
            $transport,
            $this->transportPriceProvider->getTransportPriceForSingleProduct(
                $product,
                $transport,
                $this->domain->getCurrentDomainConfig(),
                $this->currentCustomerUser->findCurrentCustomerUser(),
            ),
            $this->transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDateForProduct(
                $transport,
                $product,
                $this->domain->getId(),
            ),
        );
    }

    protected function getUsablePersonalPickupTransportForProduct(string $transportUuid, Product $product): Transport
    {
        $usableTransports = $this->transportFacade->getUsableForSingleProductOnCurrentDomainWithEagerLoadedDomainsAndTranslations(
            $product,
        );

        foreach ($usableTransports as $transport) {
            if ($transport->getUuid() !== $transportUuid) {
                continue;
            }

            if (!$transport->isPersonalPickup()) {
                throw new InvalidArgumentUserError('The stores can only be resolved for a personal pickup transport.');
            }

            return $transport;
        }

        throw new TransportNotFoundUserError(
            sprintf('Transport with UUID %s is not offered for the product.', $transportUuid),
        );
    }
}
