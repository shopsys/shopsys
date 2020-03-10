<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     * @inject
     */
    protected $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    protected $payment;

    protected function setUp(): void
    {
        $this->payment = $this->paymentFacade->getById(2);

        parent::setUp();
    }

    public function testPaymentNameByUuid(): void
    {
        $query = '
            query {
                payment(uuid: "' . $this->payment->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'payment' => [
                    'name' => t('Cash on delivery', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    /**
     * @return string
     */
    protected function getLocaleForFirstDomain(): string
    {
        return $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale();
    }
}
