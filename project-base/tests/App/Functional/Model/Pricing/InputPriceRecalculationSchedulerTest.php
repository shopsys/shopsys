<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculator;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class InputPriceRecalculationSchedulerTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    private const METHOD_WITH_VAT = 'scheduleSetInputPricesWithVat';
    private const METHOD_WITHOUT_VAT = 'scheduleSetInputPricesWithoutVat';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     * @inject
     */
    private $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculationScheduler
     * @inject
     */
    private $inputPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    private $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     * @inject
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface
     * @inject
     */
    private $paymentDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface
     * @inject
     */
    private $transportDataFactory;

    public function testOnKernelResponseNoAction()
    {
        $inputPriceRecalculatorMock = $this->getMockBuilder(InputPriceRecalculator::class)
            ->setMethods(['__construct', 'recalculateToInputPricesWithoutVat', 'recalculateToInputPricesWithVat'])
            ->disableOriginalConstructor()
            ->getMock();
        $inputPriceRecalculatorMock->expects($this->never())->method('recalculateToInputPricesWithoutVat');
        $inputPriceRecalculatorMock->expects($this->never())->method('recalculateToInputPricesWithVat');

        $filterResponseEventMock = $this->getMockBuilder(FilterResponseEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest'])
            ->getMock();
        $filterResponseEventMock->expects($this->any())->method('isMasterRequest')
            ->willReturn(true);

        $inputPriceRecalculationScheduler = new InputPriceRecalculationScheduler($inputPriceRecalculatorMock, $this->setting);

        $inputPriceRecalculationScheduler->onKernelResponse($filterResponseEventMock);
    }

    public function inputPricesTestDataProvider()
    {
        return [
            [
                'inputPriceWithoutVat' => Money::create(100),
                'inputPriceWithVat' => Money::create(121),
                'vatPercent' => '21',
            ],
            [
                'inputPriceWithoutVat' => Money::create('17261.983471'),
                'inputPriceWithVat' => Money::create(20887),
                'vatPercent' => '21',
            ],
        ];
    }

    /**
     * @dataProvider inputPricesTestDataProvider
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPriceWithVat
     * @param mixed $vatPercent
     */
    public function testOnKernelResponseRecalculateInputPricesWithoutVat(
        Money $inputPriceWithoutVat,
        Money $inputPriceWithVat,
        $vatPercent
    ) {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);

        $this->doTestOnKernelResponseRecalculateInputPrices($inputPriceWithVat, $inputPriceWithoutVat, $vatPercent, self::METHOD_WITHOUT_VAT);
    }

    /**
     * @dataProvider inputPricesTestDataProvider
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPriceWithVat
     * @param mixed $vatPercent
     */
    public function testOnKernelResponseRecalculateInputPricesWithVat(
        Money $inputPriceWithoutVat,
        Money $inputPriceWithVat,
        $vatPercent
    ) {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);

        $this->doTestOnKernelResponseRecalculateInputPrices($inputPriceWithoutVat, $inputPriceWithVat, $vatPercent, self::METHOD_WITH_VAT);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $expectedPrice
     * @param mixed $vatPercent
     * @param string $scheduleSetInputPricesMethod
     */
    private function doTestOnKernelResponseRecalculateInputPrices(Money $inputPrice, Money $expectedPrice, $vatPercent, string $scheduleSetInputPricesMethod): void
    {
        $em = $this->getEntityManager();

        $paymentData = $this->paymentDataFactory->create();
        $transportData = $this->transportDataFactory->create();

        $paymentData->pricesIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $inputPrice;
        $transportData->pricesIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $inputPrice;

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $availabilityData = new AvailabilityData();
        $availabilityData->dispatchTime = 0;
        $availability = new Availability($availabilityData);
        $em->persist($vat);
        $em->persist($availability);

        $paymentData->name = ['cs' => 'name'];

        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->paymentFacade->create($paymentData);

        $transportData->name = ['cs' => 'name'];
        $transportData->description = ['cs' => 'desc'];
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->transportFacade->create($transportData);

        $em->flush();

        $filterResponseEventMock = $this->getMockBuilder(FilterResponseEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest'])
            ->getMock();
        $filterResponseEventMock->expects($this->any())->method('isMasterRequest')
            ->willReturn(true);

        if ($scheduleSetInputPricesMethod === self::METHOD_WITH_VAT) {
            $this->inputPriceRecalculationScheduler->scheduleSetInputPricesWithVat();
        } elseif ($scheduleSetInputPricesMethod === self::METHOD_WITHOUT_VAT) {
            $this->inputPriceRecalculationScheduler->scheduleSetInputPricesWithoutVat();
        }

        $this->inputPriceRecalculationScheduler->onKernelResponse($filterResponseEventMock);

        $em->refresh($payment);
        $em->refresh($transport);

        $this->assertThat($payment->getPrice(Domain::FIRST_DOMAIN_ID)->getPrice(), new IsMoneyEqual($expectedPrice));
        $this->assertThat($transport->getPrice(Domain::FIRST_DOMAIN_ID)->getPrice(), new IsMoneyEqual($expectedPrice));
    }
}
