<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TransportCanBeUsedValidator extends ConstraintValidator
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    protected $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    protected $transportPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        Domain $domain,
        TransportPriceCalculation $transportPriceCalculation,
        CurrencyFacade $currencyFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->domain = $domain;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TransportCanBeUsed) {
            throw new UnexpectedTypeException(
                $constraint,
                TransportCanBeUsed::class
            );
        }
        // Field types and content is assured by GraphQL type definition
        $uuid = $value['uuid'];
        $priceWithoutVat = $value['price']['priceWithoutVat'];
        $priceWithVat = $value['price']['priceWithVat'];
        $vatAmount = $value['price']['vatAmount'];

        try {
            $transportEntity = $this->transportFacade->getByUuid($uuid);
            if (!$transportEntity->isEnabled($this->domain->getId())) {
                throw new TransportNotFoundException('Transport is disabled on domain');
            }
        } catch (TransportNotFoundException $exception) {
            $this->addViolationWithCodeToContext(
                $constraint->transportNotFoundMessage,
                TransportCanBeUsed::TRANSPORT_NOT_FOUND_ERROR,
                $uuid
            );
            return;
        }

        $transportPrice = $this->transportPriceCalculation->calculateIndependentPrice(
            $transportEntity,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId()
        );

        if ($transportPrice->getPriceWithoutVat()->equals($priceWithoutVat) &&
            $transportPrice->getPriceWithVat()->equals($priceWithVat) &&
            $transportPrice->getVatAmount()->equals($vatAmount)
        ) {
            return;
        }

        $this->addViolationWithCodeToContext(
            $constraint->pricesDoesNotMatchMessage,
            TransportCanBeUsed::PRICES_DOES_NOT_MATCH_ERROR,
            $uuid
        );
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
