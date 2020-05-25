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
     * @param bool $isMainVariant
     */
    public function validate(array $akeneoProductData, bool $isMainVariant): void
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
        ];
        if ($isMainVariant === false) {
            $fieldsValidationSetup['enabled'] = [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'bool']),
            ];
        } else {
            $fieldsValidationSetup['family'] = [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
            ];
            $fieldsValidationSetup['family_variant'] = [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
            ];
        }

        $violations = $this->validator->validate($akeneoProductData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => $fieldsValidationSetup,
        ]));

        $priceValidationSetup = [
            new Assert\Type('string'),
            new Assert\Length(['max' => 20]),
        ];
        $this->validatePriceValue($violations, $akeneoProductData['values'], 'low_price_vat', $priceValidationSetup);

        $highPriceValidationSetup = $priceValidationSetup;
        if ($isMainVariant === false) {
            $highPriceValidationSetup[] = new Assert\NotBlank();
        }
        $this->validatePriceValue($violations, $akeneoProductData['values'], 'high_price_vat', $highPriceValidationSetup);

        $this->validateValueData($violations, $akeneoProductData['values'] ?? null, 'ean', [
            new Assert\Type(['type' => 'numeric']),
            new Assert\Length(['max' => 100]),
        ]);

        $this->validateLocalizedData($violations, $akeneoProductData['values'] ?? null, 'product_type', [
            new Assert\NotBlank(),
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
        array $asserts
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
    protected function validatePriceValue(
        ConstraintViolationListInterface $violations,
        ?array $data,
        string $validateKeyName,
        array $asserts
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
                                    'data' => new Assert\All(
                                        new Assert\Collection([
                                            'allowExtraFields' => true,
                                            'fields' => [
                                                'currency' => new Assert\Required([
                                                    new Assert\NotNull(),
                                                ]),
                                                'amount' => new Assert\Required($asserts),
                                            ],
                                        ])
                                    ),
                                ],
                            ]),
                        ]),
                    ]),
                ],
        ])
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
        array $asserts
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
     * @param \Symfony\Component\Validator\Constraint[] $asserts
     * @return \Symfony\Component\Validator\Constraints\NotBlank|null
     */
    private function findNotBlankAssert(array $asserts): ?Assert\NotBlank
    {
        $notBlankAssert = null;
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
        string $validateKeyName
    ): void {
        $violation = new ConstraintViolation(
            $message,
            '',
            [],
            '',
            $validateKeyName,
            null
        );
        $violations->add($violation);
    }
}
