<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\CountryFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class OrderDataFactory
{
    protected const ORDER_ORIGIN_GRAPHQL = 'Frontend API';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    protected $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    protected $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\CountryFacade
     */
    protected $countryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface
     */
    protected $orderDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactoryInterface $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryFacade $countryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        OrderDataFactoryInterface $orderDataFactory,
        Domain $domain,
        PaymentFacade $paymentFacade,
        TransportFacade $transportFacade,
        CurrencyFacade $currencyFacade,
        CountryFacade $countryFacade,
        ProductFacade $productFacade
    ) {
        $this->domain = $domain;
        $this->paymentFacade = $paymentFacade;
        $this->transportFacade = $transportFacade;
        $this->currencyFacade = $currencyFacade;
        $this->countryFacade = $countryFacade;
        $this->orderDataFactory = $orderDataFactory;
        $this->productFacade = $productFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function createOrderDataFromArgument(Argument $argument): OrderData
    {
        $input = $argument['input'];

        $orderData = $this->orderDataFactory->create();

        foreach ($input as $key => $value) {
            if (property_exists(get_class($orderData), $key)) {
                $orderData->{$key} = $value;
            }
        }

        $orderData->domainId = $this->domain->getId();
        $orderData->origin = static::ORDER_ORIGIN_GRAPHQL;
        $orderData->deliveryAddressSameAsBillingAddress = !$input['differentDeliveryAddress'];

        $orderData = $this->withResolvedFields($input, $orderData);

        return $orderData;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[]
     */
    public function createQuantifiedProductsFromArgument(Argument $argument): array
    {
        $productsInput = $argument['input']['products'];
        $quantifiedProducts = [];

        foreach ($productsInput as $productInput) {
            $product = $this->productFacade->getByUuid($productInput['uuid']);
            $quantifiedProducts[] = new QuantifiedProduct($product, $productInput['quantity']);
        }

        return $quantifiedProducts;
    }

    /**
     * @param array{
     *     payment: array{uuid: string},
     *     transport: array{uuid: string},
     *     country: string,
     *     differentDeliveryAddress: bool,
     *     deliveryCountry?: string
     * } $input
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    protected function withResolvedFields(array $input, OrderData $orderData): OrderData
    {
        $cloneOrderData = clone $orderData;

        $cloneOrderData->payment = $this->paymentFacade->getEnabledOnDomainByUuid(
            $input['payment']['uuid'],
            $this->domain->getId()
        );

        $cloneOrderData->transport = $this->transportFacade->getEnabledOnDomainByUuid(
            $input['transport']['uuid'],
            $this->domain->getId()
        );

        $cloneOrderData->currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        $cloneOrderData->country = $this->countryFacade->findByCode($input['country']);

        if ($input['differentDeliveryAddress']) {
            $cloneOrderData->deliveryCountry = $this->countryFacade->findByCode($input['deliveryCountry']);
        }

        return $cloneOrderData;
    }
}
