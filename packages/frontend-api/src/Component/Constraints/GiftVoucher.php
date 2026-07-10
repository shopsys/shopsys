<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Attribute;
use Override;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class GiftVoucher extends Constraint
{
    public const INVALID_ERROR = '6eaa574f-b51f-4523-b117-5e7558d19c81';
    public const NO_LONGER_VALID_ERROR = 'c6a109a6-cbeb-46a1-a0c0-3a0838d73e74';
    public const ALREADY_APPLIED_GIFT_VOUCHER_ERROR = '81c948ad-0574-421f-a71d-839501f335f7';
    public const GIFT_VOUCHER_NOT_REDEEMABLE_ERROR = '44262bdd-7634-4e07-a2c2-45e666860e47';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::INVALID_ERROR => 'INVALID_ERROR',
        self::NO_LONGER_VALID_ERROR => 'NO_LONGER_VALID_ERROR',
        self::ALREADY_APPLIED_GIFT_VOUCHER_ERROR => 'ALREADY_APPLIED_GIFT_VOUCHER_ERROR',
        self::GIFT_VOUCHER_NOT_REDEEMABLE_ERROR => 'GIFT_VOUCHER_NOT_REDEEMABLE_ERROR',
    ];

    /**
     * @param array<string, mixed>|null $options
     * @param array<string>|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        public string $invalidMessage = 'The gift voucher is not valid. Check it, please.',
        public string $noLongerValidMessage = 'The gift voucher is no longer valid. Check it, please.',
        public string $alreadyAppliedGiftVoucherMessage = 'The gift voucher is already applied in the current cart.',
        public string $giftVoucherNotRedeemableMessage = 'The gift voucher has already been redeemed or cancelled.',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
