<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FutureProductStockTransferScontoBridgeValidator
{
    public const REGEX_DATETIME_WITH_OPTIONAL_MILISECONDS = '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z?/';

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
        $violations = $this->validator->validate($scontoBridgeCustomerData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'sku' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ],
                'storeCode' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                ],
                'amount' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'int']),
                ],
                'dateExpectedArrival' => [
                    new Assert\NotBlank(),
                ],
                'modificationTime' => [
                    new Assert\NotBlank(),
                    new Assert\Regex(['pattern' => self::REGEX_DATETIME_WITH_OPTIONAL_MILISECONDS]),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            throw new TransferInvalidDataAdministratorCriticalException($violations);
        }
    }
}
