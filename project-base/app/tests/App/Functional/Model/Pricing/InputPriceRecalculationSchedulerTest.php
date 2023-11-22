<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceRecalculator;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tests\App\Test\TransactionFunctionalTestCase;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class InputPriceRecalculationSchedulerTest extends TransactionFunctionalTestCase
{
    private const METHOD_WITH_VAT = 'scheduleSetInputPricesWithVat';

    private const METHOD_WITHOUT_VAT = 'scheduleSetInputPricesWithoutVat';

    /**
     * @inject
     */
    private Setting $setting;

    /**
     * @inject
     */
    private InputPriceRecalculationScheduler $inputPriceRecalculationScheduler;

    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private TransportFacade $transportFacade;

    /**
     * @inject
     */
    private PaymentDataFactoryInterface $paymentDataFactory;

    /**
     * @inject
     */
    private TransportDataFactoryInterface $transportDataFactory;

    public function testOnKernelResponseNoAction(): void
    {
        $inputPriceRecalculatorMock = $this->getMockBuilder(InputPriceRecalculator::class)
            ->onlyMethods(['__construct', 'recalculateToInputPricesWithoutVat', 'recalculateToInputPricesWithVat'])
            ->disableOriginalConstructor()
            ->getMock();
        $inputPriceRecalculatorMock->expects($this->never())->method('recalculateToInputPricesWithoutVat');
        $inputPriceRecalculatorMock->expects($this->never())->method('recalculateToInputPricesWithVat');

        $inputPriceRecalculationScheduler = new InputPriceRecalculationScheduler(
            $inputPriceRecalculatorMock,
            $this->setting,
        );

        $responseEvent = new ResponseEvent(
            self::$kernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        $inputPriceRecalculationScheduler->onKernelResponse($responseEvent);
    }

    /**
     * @return array<int, array<'inputPriceWithoutVat'|'inputPriceWithVat'|'vatPercent', \Shopsys\FrameworkBundle\Component\Money\Money|'21'>>
     */
    public function inputPricesTestDataProvider(): array
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
     * @param string $vatPercent
     */
    public function testOnKernelResponseRecalculateInputPricesWithoutVat(
        Money $inputPriceWithoutVat,
        Money $inputPriceWithVat,
        string $vatPercent,
    ): void {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);

        $this->doTestOnKernelResponseRecalculateInputPrices(
            $inputPriceWithVat,
            $inputPriceWithoutVat,
            $vatPercent,
            self::METHOD_WITHOUT_VAT,
        );
    }

    /**
     * @dataProvider inputPricesTestDataProvider
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPriceWithVat
     * @param string $vatPercent
     */
    public function testOnKernelResponseRecalculateInputPricesWithVat(
        Money $inputPriceWithoutVat,
        Money $inputPriceWithVat,
        string $vatPercent,
    ): void {
        $this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);

        $this->doTestOnKernelResponseRecalculateInputPrices(
            $inputPriceWithoutVat,
            $inputPriceWithVat,
            $vatPercent,
            self::METHOD_WITH_VAT,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $expectedPrice
     * @param string $vatPercent
     * @param string $scheduleSetInputPricesMethod
     */
    private function doTestOnKernelResponseRecalculateInputPrices(
        Money $inputPrice,
        Money $expectedPrice,
        string $vatPercent,
        string $scheduleSetInputPricesMethod,
    ): void {
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
        $this->em->persist($vat);
        $this->em->persist($availability);

        $paymentData->name = ['cs' => 'name'];

        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->paymentFacade->create($paymentData);

        $transportData->name = ['cs' => 'name'];
        $transportData->description = ['cs' => 'desc'];
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->transportFacade->create($transportData);

        $this->em->flush();

        $responseEvent = new ResponseEvent(
            self::$kernel,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response(),
        );

        if ($scheduleSetInputPricesMethod === self::METHOD_WITH_VAT) {
            $this->inputPriceRecalculationScheduler->scheduleSetInputPricesWithVat();
        } elseif ($scheduleSetInputPricesMethod === self::METHOD_WITHOUT_VAT) {
            $this->inputPriceRecalculationScheduler->scheduleSetInputPricesWithoutVat();
        }

        $this->inputPriceRecalculationScheduler->onKernelResponse($responseEvent);

        $this->em->refresh($payment);
        $this->em->refresh($transport);

        $this->assertThat($payment->getPrice(Domain::FIRST_DOMAIN_ID)->getPrice(), new IsMoneyEqual($expectedPrice));
        $this->assertThat($transport->getPrice(Domain::FIRST_DOMAIN_ID)->getPrice(), new IsMoneyEqual($expectedPrice));
    }
}
