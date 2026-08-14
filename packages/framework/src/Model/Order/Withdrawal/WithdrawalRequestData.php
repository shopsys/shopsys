<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Withdrawal;

class WithdrawalRequestData
{
    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var string
     */
    public $email;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData|null
     */
    public $telephone;

    /**
     * @var string|null
     */
    public $note;

    /**
     * @var \DateTimeImmutable|null
     */
    public $requestedAt;

    /**
     * @var bool
     */
    public $confirmed;

    /**
     * @var string|null
     */
    public $confirmationHash;

    public function __construct()
    {
        $this->confirmed = false;
    }
}
