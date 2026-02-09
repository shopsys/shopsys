<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WatchdogValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Watchdog) {
            throw new UnexpectedTypeException($constraint, Watchdog::class);
        }

        if ($this->transformStringHelper->emptyToNull($value) === null) {
            return;
        }

        $productUuid = $value;

        try {
            $product = $this->productFacade->getByUuid($productUuid);

            if ($product->isMainVariant()) {
                $this->addViolationWithCodeToContext($constraint->notAvailableMainVariant, Watchdog::MAIN_VARIANT_ERROR);

                return;
            }

            if ($product->getProductType() === ProductTypeEnum::TYPE_INQUIRY) {
                $this->addViolationWithCodeToContext($constraint->notAvailableInquiry, Watchdog::INQUIRY_ERROR);

                return;
            }
        } catch (ProductNotFoundException) {
            $this->addViolationWithCodeToContext($constraint->productNotFound, Watchdog::PRODUCT_NOT_FOUND_ERROR);
        }
    }

    protected function addViolationWithCodeToContext(string $message, string $code): void
    {
        $this->context->buildViolation($message)
            ->setCode($code)
            ->atPath('watchdog')
            ->addViolation();
    }
}
