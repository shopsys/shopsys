<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductTransferAkeneoValidator
{
    /**
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(protected ValidatorInterface $validator)
    {
    }

    /**
     * @param array $akeneoProductData
     */
    public function validate(array $akeneoProductData): void
    {
        $fieldsValidationSetup = [
            'identifier' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
                new Assert\Length(['max' => 100]),
            ],
            'categories' => new Assert\Optional([
                new Assert\All([
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255]),
                ]),
            ]),
            // key `values` is mandatory, because some inner fields cannot be empty (eg. `product_type`)
            'values' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'array']),
            ],
            'enabled' => [
                new Assert\Type(['type' => 'bool']),
            ],
        ];

        $violations = $this->validator->validate($akeneoProductData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => $fieldsValidationSetup,
        ]));

        $this->validateValueData($violations, $akeneoProductData['values'] ?? null, 'ean', [
            new Assert\Type(['type' => 'numeric']),
            new Assert\Length(['max' => 100]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'product_type', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 20]),
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

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'delivery_method_parcel_allowed', [
            new Assert\Type(['type' => 'bool']),
        ]);

        if (count($violations) > 0) {
            throw new TransferInvalidDataAdministratorCriticalException($violations);
        }
    }

    /**
     * @param array $akeneoProductData
     */
    public function validateIdentifier(array $akeneoProductData): void
    {
        $fieldsValidationSetup = [
            'identifier' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
                new Assert\Length(['max' => 100]),
            ],
        ];

        $violations = $this->validator->validate($akeneoProductData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => $fieldsValidationSetup,
        ]));

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
    private function validateValueData(
        ConstraintViolationListInterface $violations,
        ?array $data,
        string $validateKeyName,
        array $asserts,
    ): void {
        if ($data === null || !array_key_exists($validateKeyName, $data)) {
            $notBlankAssert = $this->findNotBlankAssert($asserts);

            if ($notBlankAssert !== null) {
                $this->addNewViolation($violations, $notBlankAssert->message, $validateKeyName);
            }

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
                                    $asserts,
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
    protected function validatePriceValue(
        ConstraintViolationListInterface $violations,
        ?array $data,
        string $validateKeyName,
        array $asserts,
    ): void {
        if ($data === null || !array_key_exists($validateKeyName, $data)) {
            $notBlankAssert = $this->findNotBlankAssert($asserts);

            if ($notBlankAssert !== null) {
                $this->addNewViolation($violations, $notBlankAssert->message, $validateKeyName);
            }

            return;
        }

        $violations->addAll($this->validator->validate(
            $data,
            new Assert\Collection([
                'allowExtraFields' => true,
                'fields' => [
                    $validateKeyName => new Assert\Optional([
                        new Assert\All([
                            new Assert\Collection([
                                'allowExtraFields' => true,
                                'fields' => [
                                    'data' => new Assert\Collection([
                                        'fields' => [
                                            '0' => new Assert\Collection([
                                                'allowExtraFields' => true,
                                                'fields' => [
                                                    'currency' => new Assert\Required([
                                                        new Assert\NotNull(),
                                                    ]),
                                                    'amount' => new Assert\Required($asserts),
                                                ],
                                            ]),
                                            '1' => new Assert\Collection([
                                                'allowExtraFields' => true,
                                                'fields' => [
                                                    'currency' => new Assert\Required([
                                                        new Assert\NotNull(),
                                                    ]),
                                                    'amount' => new Assert\Required($asserts),
                                                ],
                                            ]),
                                        ],
                                        'allowMissingFields' => true,
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
            ]),
        ));
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
        array $asserts,
    ): void {
        if ($data === null || !array_key_exists($validateKeyName, $data)) {
            $notBlankAssert = $this->findNotBlankAssert($asserts);

            if ($notBlankAssert !== null) {
                $this->addNewViolation($violations, $notBlankAssert->message, $validateKeyName);
            }

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
                                    $asserts,
                                ),
                            ],
                        ]),
                    ]),
                ]),
            ],
        ])));
    }

    /**
     * @param \Symfony\Component\Validator\Constraint[] $asserts
     * @return \Symfony\Component\Validator\Constraints\NotBlank|null
     */
    private function findNotBlankAssert(array $asserts): ?Assert\NotBlank
    {
        foreach ($asserts as $assert) {
            if ($assert instanceof Assert\NotBlank) {
                return $assert;
            }
        }

        return null;
    }

    /**
     * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
     * @param string $message
     * @param string $validateKeyName
     */
    private function addNewViolation(
        ConstraintViolationListInterface $violations,
        string $message,
        string $validateKeyName,
    ): void {
        $violation = new ConstraintViolation(
            $message,
            '',
            [],
            '',
            $validateKeyName,
            null,
        );
        $violations->add($violation);
    }
}
