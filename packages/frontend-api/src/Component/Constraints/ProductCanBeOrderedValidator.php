<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade as FrontendApiProductFacade;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ProductCanBeOrderedValidator extends ConstraintValidator
{
    protected ProductCachedAttributesFacade $productCachedAttributesFacade;

    protected Domain $domain;

    protected CurrentCustomerUser $currentCustomerUser;

    protected FrontendApiProductFacade $frontendApiProductFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $frontendApiProductFacade
     */
    public function __construct(
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        FrontendApiProductFacade $frontendApiProductFacade
    ) {
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->frontendApiProductFacade = $frontendApiProductFacade;
    }

    /**
     * @param mixed $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ProductCanBeOrdered) {
            throw new UnexpectedTypeException($constraint, ProductCanBeOrdered::class);
        }

        // Field types and content is assured by GraphQL type definition
        $uuid = $value['uuid'];
        $priceWithoutVat = $value['unitPrice']['priceWithoutVat'];
        $priceWithVat = $value['unitPrice']['priceWithVat'];
        $vatAmount = $value['unitPrice']['vatAmount'];

        try {
            $productEntity = $this->frontendApiProductFacade->getSellableByUuid(
                $uuid,
                $this->domain->getId(),
                $this->currentCustomerUser->getPricingGroup()
            );
        } catch (ProductNotFoundException $exception) {
            $this->addViolationWithCodeToContext(
                $constraint->productNotFoundMessage,
                ProductCanBeOrdered::PRODUCT_NOT_FOUND_ERROR,
                $uuid
            );
            return;
        }

        $sellingPrice = $this->productCachedAttributesFacade->getProductSellingPrice($productEntity);

        if ($sellingPrice === null) {
            $this->addViolationWithCodeToContext(
                $constraint->noSellingPriceMessage,
                ProductCanBeOrdered::NO_SELLING_PRICE_ERROR,
                $uuid
            );
            return;
        }

        if ($sellingPrice->getPriceWithoutVat()->equals($priceWithoutVat) &&
            $sellingPrice->getPriceWithVat()->equals($priceWithVat) &&
            $sellingPrice->getVatAmount()->equals($vatAmount)
        ) {
            return;
        }

        $this->addViolationWithCodeToContext(
            $constraint->pricesDoesNotMatchMessage,
            ProductCanBeOrdered::PRICES_DOES_NOT_MATCH_ERROR,
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
