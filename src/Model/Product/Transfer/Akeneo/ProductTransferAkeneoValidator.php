<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductTransferAkeneoValidator
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
     * @param array $akeneoProductData
     */
    public function validate(array $akeneoProductData): void
    {
        $violations = $this->validator->validate($akeneoProductData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'identifier' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 100]),
                ],
            ],
        ]));

        $this->validateValueData($violations, $akeneoProductData['values'] ?? null, 'ean', [
            new Assert\Type(['type' => 'numeric']),
            new Assert\Length(['max' => 100]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'name_prefix', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'name', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'name_sufix', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'usp1', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'usp2', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'usp3', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'usp4', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'usp5', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'description', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 65534]),
        ]);

        if (count($violations) > 0) {
            throw new TransferInvalidDataAdministratorCriticalException($violations);
        }
    }

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @param array|null $data
     * @param string $validateKeyName
     * @param array $asserts
     */
    protected function validateValueData(
        ConstraintViolationListInterface $violations,
        ?array $data,
        string $validateKeyName,
        array $asserts
    ): void {
        if ($data === null) {
            return;
        }

        if (!array_key_exists($validateKeyName, $data)) {
            return;
        }

        $violations->addAll($this->validator->validate($data, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                $validateKeyName => new Assert\Optional([
                    new Assert\Collection([
                        new Assert\Collection([
                            'allowExtraFields' => true,
                            'fields' => [
                                'locale' => new Assert\Required([
                                    new Assert\IsNull(),
                                ]),
                                'data' => new Assert\Required(
                                    $asserts
                                ),
                            ],
                        ]),
                    ]),
                ]),
            ],
        ])));
    }

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @param array|null $data
     * @param string $validateKeyName
     * @param array $asserts
     */
    private function validateLocalizedData(
        ConstraintViolationListInterface $violations,
        ?array $data,
        string $validateKeyName,
        array $asserts
    ): void {
        if ($data === null) {
            return;
        }

        if (!array_key_exists($validateKeyName, $data)) {
            return;
        }

        $violations->addAll($this->validator->validate($data, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                $validateKeyName => new Assert\Optional([
                    new Assert\All([
                        new Assert\Collection([
                            'allowExtraFields' => true,
                            'fields' => [
                                'locale' => new Assert\Required([
                                    new Assert\NotNull(),
                                ]),
                                'data' => new Assert\Required(
                                    $asserts
                                ),
                            ],
                        ]),
                    ]),
                ]),
            ],
        ])));
    }
}
