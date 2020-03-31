<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Payment\Exception\PaymentNotFoundException;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PaymentCanBeUsedValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade
     */
    protected $paymentFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    protected $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        PaymentFacade $paymentFacade,
        Domain $domain,
        PaymentPriceCalculation $paymentPriceCalculation,
        CurrencyFacade $currencyFacade
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->domain = $domain;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PaymentCanBeUsed) {
            throw new \Symfony\Component\Validator\Exception\UnexpectedTypeException($constraint, PaymentCanBeUsed::class);
        }
        // Field types and content is assured by GraphQL type definition
        $uuid = $value['uuid'];
        $priceWithoutVat = $value['price']['priceWithoutVat'];
        $priceWithVat = $value['price']['priceWithVat'];
        $vatAmount = $value['price']['vatAmount'];

        try {
            $paymentEntity = $this->paymentFacade->getByUuid($uuid);
            if (!$paymentEntity->isEnabled($this->domain->getId())) {
                throw new PaymentNotFoundException('Payment is disabled on domain');
            }
        } catch (PaymentNotFoundException $exception) {
            $this->addViolationWithCodeToContext($constraint->paymentNotFoundMessage, PaymentCanBeUsed::PAYMENT_NOT_FOUND_ERROR, $uuid);
            return;
        }

        $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice(
            $paymentEntity,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId()
        );

        if (!$paymentPrice->getPriceWithoutVat()->equals($priceWithoutVat) ||
            !$paymentPrice->getPriceWithVat()->equals($priceWithVat) ||
            !$paymentPrice->getVatAmount()->equals($vatAmount)
        ) {
            $this->addViolationWithCodeToContext($constraint->pricesDoesNotMatchMessage, PaymentCanBeUsed::PRICES_DOES_NOT_MATCH_ERROR, $uuid);
        }
    }

    /**
     * @param string $message
     * @param string $code
     * @param string $uuid
     */
    protected function addViolationWithCodeToContext(string $message, string $code, string $uuid): void
    {
        $this->context->buildViolation($message)
            ->setParameter('{{ uuid }}', $uuid)
            ->setCode($code)
            ->addViolation();
    }
}
