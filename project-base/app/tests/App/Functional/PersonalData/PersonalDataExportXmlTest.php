<?php

declare(strict_types=1);

namespace Tests\App\Functional\PersonalData;

use App\DataFixtures\Demo\ComplaintStatusDataFixture;
use App\DataFixtures\Demo\CountryDataFixture;
use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\OrderStatusDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserData;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use App\Model\Order\Status\OrderStatus;
use App\Model\Product\Product;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Xml\XmlNormalizerHelper;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Watchdog\Watchdog;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogData;
use Tests\App\Functional\Model\Order\TestOrderProvider;
use Tests\App\Test\TransactionFunctionalTestCase;
use Twig\Environment;

class PersonalDataExportXmlTest extends TransactionFunctionalTestCase
{
    protected const string EMAIL = 'no-reply@shopsys.com';
    protected const string EXPECTED_XML_FILE_NAME = 'test.xml';

    /**
     * @inject
     */
    private Environment $twigEnvironment;

    /**
     * @inject
     */
    private XmlNormalizerHelper $xmlNormalizerHelper;

    public function testExportXml(): void
    {
        $country = $this->getReference(CountryDataFixture::COUNTRY_CZECH_REPUBLIC, Country::class);

        $customerData = new CustomerData();
        $customerData->domainId = Domain::FIRST_DOMAIN_ID;
        $customer = new Customer($customerData);

        $customerData->billingAddress = $this->createBillingAddress($country, $customer);
        $deliveryAddress = $this->createDeliveryAddress($country, $customer);
        $customerData->deliveryAddresses[] = $deliveryAddress;

        $customer->edit($customerData);

        $customerUser = $this->createCustomerUser($customer);
        $currency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $status = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW, OrderStatus::class);
        $order = $this->createOrder($currency, $status, $country);
        $product = $this->createMock(Product::class);
        $price = new Price(Money::create(1), Money::create(1));
        $orderItem = new OrderItem($order, 'test', $price, '1', 1, OrderItemTypeEnum::TYPE_PRODUCT, 'ks', 'cat');
        $orderItem->setProduct($product);
        $order->addItem($orderItem);
        $order->setStatus($status);

        $complaint = $this->createComplaint($country, $order, $customerUser);

        $watchdogProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $watchdog = $this->createWatchdog($customerUser->getEmail(), $watchdogProduct);

        $generatedXml = $this->twigEnvironment->render('@ShopsysFramework/Front/Content/PersonalData/export.xml.twig', [
            'customerUser' => $customerUser,
            'orders' => [
                0 => $order,
            ],
            'newsletterSubscriber' => null,
            'complaints' => [$complaint],
            'watchdogs' => [$watchdog],
        ]);

        $generatedXml = $this->xmlNormalizerHelper->normalizeXml($generatedXml);

        $expectedXml = file_get_contents(__DIR__ . '/Resources/' . self::EXPECTED_XML_FILE_NAME);
        $expectedXml = str_replace(['{complaint_status}', '{order_status}'], [$complaint->getStatus()->getName($this->getFirstDomainLocale()), $status->getName($this->getFirstDomainLocale())], $expectedXml);
        $expectedXml = $this->xmlNormalizerHelper->normalizeXml($expectedXml);

        $this->assertSame($expectedXml, $generatedXml);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    private function createBillingAddress(Country $country, Customer $customer): BillingAddress
    {
        $billingAddressData = new BillingAddressData();
        $billingAddressData->country = $country;
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->street = 'Hlubinská';
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->companyNumber = '123456';
        $billingAddressData->companyTaxNumber = '123456';
        $billingAddressData->postcode = '70200';
        $billingAddressData->customer = $customer;

        return new BillingAddress($billingAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    private function createDeliveryAddress(Country $country, Customer $customer): DeliveryAddress
    {
        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->country = $country;
        $deliveryAddressData->telephone = '+420987654321';
        $deliveryAddressData->postcode = '70200';
        $deliveryAddressData->companyName = 'Shopsys';
        $deliveryAddressData->street = 'Hlubinská';
        $deliveryAddressData->city = 'Ostrava';
        $deliveryAddressData->lastName = 'Fero';
        $deliveryAddressData->firstName = 'Mrkva';
        $deliveryAddressData->customer = $customer;

        return new DeliveryAddress($deliveryAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \App\Model\Customer\User\CustomerUser
     */
    private function createCustomerUser(Customer $customer): CustomerUser
    {
        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID, PricingGroup::class);

        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'Jaromír';
        $customerUserData->lastName = 'Jágr';
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserData->createdAt = new DateTime('2018-04-13');
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->telephone = '+420987654321';
        $customerUserData->customer = $customer;
        $customerUserData->pricingGroup = $pricingGroup;

        return new CustomerUser($customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \App\Model\Order\Status\OrderStatus $status
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @return \App\Model\Order\Order
     */
    private function createOrder(Currency $currency, OrderStatus $status, Country $country)
    {
        $orderData = TestOrderProvider::getTestOrderData();
        $orderData->currency = $currency;
        $orderData->status = $status;
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->createdAt = new DateTime('2018-04-13');
        $orderData->domainId = Domain::FIRST_DOMAIN_ID;
        $orderData->lastName = 'Bořič';
        $orderData->firstName = 'Adam';
        $orderData->city = 'Liberec';
        $orderData->street = 'Cihelní 5';
        $orderData->companyName = 'Shopsys';
        $orderData->isCompanyCustomer = true;
        $orderData->postcode = '65421';
        $orderData->telephone = '+420987654321';
        $orderData->companyTaxNumber = '123456';
        $orderData->companyNumber = '123456';
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->country = $country;

        return new Order($orderData, '1523596513', 'hash');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    private function createComplaint(Country $country, Order $order, CustomerUser $customerUser): Complaint
    {
        $complaintStatus = $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class);

        $complaintData = new ComplaintData();
        $complaintData->domainId = Domain::FIRST_DOMAIN_ID;
        $complaintData->number = '1523596513';
        $complaintData->order = $order;
        $complaintData->createdAt = new DateTime('2018-04-13');
        $complaintData->customerUser = $customerUser;
        $complaintData->deliveryFirstName = 'Adam';
        $complaintData->deliveryLastName = 'Bořič';
        $complaintData->deliveryCompanyName = 'Shopsys';
        $complaintData->deliveryTelephone = '+420987654321';
        $complaintData->deliveryStreet = 'Cihelní 5';
        $complaintData->deliveryCity = 'Liberec';
        $complaintData->deliveryPostcode = '65421';
        $complaintData->deliveryCountry = $country;
        $complaintData->status = $complaintStatus;

        $complaintItem = $this->createMock(ComplaintItem::class);

        return new Complaint($complaintData, [$complaintItem]);
    }

    /**
     * @param string $email
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Watchdog\Watchdog
     */
    private function createWatchdog(string $email, Product $product): Watchdog
    {
        $watchdogData = new WatchdogData();
        $watchdogData->domainId = Domain::FIRST_DOMAIN_ID;
        $watchdogData->email = $email;
        $watchdogData->product = $product;
        $watchdogData->createdAt = new DateTime('2018-04-13');
        $watchdogData->updatedAt = new DateTime('2018-04-13');
        $watchdogData->validUntil = new DateTime('2020-04-13');

        return new Watchdog($watchdogData);
    }
}
