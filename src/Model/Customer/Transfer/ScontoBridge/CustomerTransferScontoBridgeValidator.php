<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerTransferScontoBridgeValidator
{
    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array $scontoBridgeCustomerData
     */
    public function validate(array $scontoBridgeCustomerData): void
    {
    }
}
