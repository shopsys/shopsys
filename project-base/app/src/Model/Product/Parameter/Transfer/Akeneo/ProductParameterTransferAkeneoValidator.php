<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductParameterTransferAkeneoValidator
{
    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(protected ValidatorInterface $validator)
    {
    }

    /**
     * @param array $akeneoCategoryData
     */
    public function validate(array $akeneoCategoryData): void
    {
        $violations = $this->validator->validate($akeneoCategoryData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'code' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 100]),
                    new Assert\Regex([
                        'pattern' => '/^param__(\S)*$/',
                    ]),
                ],
                'type' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 100]),
                ],
                'group' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 100]),
                    new Assert\Regex([
                        'pattern' => '/^param__(\S)*$/',
                    ]),
                ],
                'sort_order' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'int']),
                ],
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
