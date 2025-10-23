<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

class OrderWithdrawalRequest extends Constraint
{
    public const string ORDER_NOT_FOUND_ERROR = 'a1b2c3d4-1111-2222-3333-4444aaaabbbb';
    public const string ORDER_CANCELLED_ERROR = 'a1b2c3d4-1111-2222-3333-5555bbbbcccc';
    public const string WITHDRAWAL_DEADLINE_PASSED_ERROR = 'a1b2c3d4-1111-2222-3333-6666ccccdddd';
    public const string ALREADY_REQUESTED_ERROR = 'a1b2c3d4-1111-2222-3333-7777ddddeeee';

    public string $orderNotFoundMessage = 'Order not found';

    public string $orderCancelledMessage = 'Withdrawal is not allowed for cancelled orders';

    public string $withdrawalDeadlinePassedMessage = 'Withdrawal deadline has passed for this order';

    public string $alreadyRequestedMessage = 'Withdrawal has already been requested for this order';

    /**
     * @var array<string, string>
     */
    protected const array ERROR_NAMES = [
        self::ORDER_NOT_FOUND_ERROR => 'ORDER_NOT_FOUND_ERROR',
        self::ORDER_CANCELLED_ERROR => 'ORDER_CANCELLED_ERROR',
        self::WITHDRAWAL_DEADLINE_PASSED_ERROR => 'WITHDRAWAL_DEADLINE_PASSED_ERROR',
        self::ALREADY_REQUESTED_ERROR => 'ALREADY_REQUESTED_ERROR',
    ];

    /**
     * @return string|array
     */
    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
