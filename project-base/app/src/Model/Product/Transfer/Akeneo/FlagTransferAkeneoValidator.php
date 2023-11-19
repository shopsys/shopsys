<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FlagTransferAkeneoValidator
{
    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(protected ValidatorInterface $validator)
    {
    }

    /**
     * @param mixed[] $akeneoFlagData
     */
    public function validate(array $akeneoFlagData): void
    {
        $violations = $this->validator->validate($akeneoFlagData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'labels' => new Assert\Collection([
                    'allowExtraFields' => true,
                    'fields' => [
                        'cs_CZ' => [
                            new Assert\NotBlank(),
                            new Assert\Type(['type' => 'string']),
                            new Assert\Length(['max' => 255]),
                        ],
                        'sk_SK' => [
                            new Assert\NotBlank(),
                            new Assert\Type(['type' => 'string']),
                            new Assert\Length(['max' => 255]),
                        ],
                    ],
                ]),
            ],
        ]));

        if (count($violations) > 0) {
            throw new TransferInvalidDataAdministratorCriticalException($violations);
        }
    }
}
