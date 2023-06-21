<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PromoCode extends Constraint
{
    public const INVALID_ERROR = '4693d59e-98c5-4cf8-8fcd-1577252f27f6';
    public const NOT_YET_VALID_ERROR = 'fe8c66c0-6550-4e36-a30b-456e2afd23fc';
    public const NO_LONGER_VALID_ERROR = 'c9faedd9-2898-4f30-a10a-267c5f6d6bc3';
    public const NO_RELATION_TO_PRODUCTS_IN_CART_ERROR = '2c164e80-618f-4a2f-953e-56cc0035a079';
    public const FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR = 'db24a391-6f95-482e-b555-2cb750c68ad6';
    public const NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR = 'f9003c4b-4036-4625-b1c3-7b1c82c461f3';
    public const ALREADY_APPLIED_PROMO_CODE_ERROR = 'bde9e59e-6881-460e-8501-7f5e9a57a266';
    public const LIMIT_NOT_REACHED_ERROR = '3f94ee5e-b496-441b-9744-d8b6375000e6';

    public string $invalidMessage = 'The promo code is not valid or it has been already used. Check it, please.';

    public string $notYetValidMessage = 'The promo code is not valid yet. Check it, please.';

    public string $noLongerValidMessage = 'The promo code is no longer valid. Check it, please.';

    public string $noRelationToProductsInCartMessage = 'The promo code is not applicable to any of the products in your cart. Check it, please.';

    public string $forRegisteredCustomerUsersOnlyMessage = 'Promo code is available for registered customers only.';

    public string $notAvailableForCustomerUserPricingGroupMessage = 'Promo code is not available for your pricing group. Maybe you forgot to log in.';

    public string $alreadyAppliedPromoCodeMessage = 'Promo code is already applied in the current cart.';

    public string $limitNotReachedMessage = 'The promo code can only be used for a higher total price.';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected static $errorNames = [
        self::INVALID_ERROR => 'INVALID_ERROR',
        self::NOT_YET_VALID_ERROR => 'NOT_YET_VALID_ERROR',
        self::NO_LONGER_VALID_ERROR => 'NO_LONGER_VALID_ERROR',
        self::NO_RELATION_TO_PRODUCTS_IN_CART_ERROR => 'NO_RELATION_TO_PRODUCTS_IN_CART_ERROR',
        self::FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR => 'FOR_REGISTERED_CUSTOMER_USERS_ONLY_ERROR',
        self::NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR => 'NOT_AVAILABLE_FOR_CUSTOMER_USER_PRICING_GROUP_ERROR',
        self::ALREADY_APPLIED_PROMO_CODE_ERROR => 'ALREADY_APPLIED_PROMO_CODE_ERROR',
        self::LIMIT_NOT_REACHED_ERROR => 'LIMIT_NOT_REACHED_ERROR',
    ];

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
