<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Import;

use App\Component\Akeneo\Transfer\Exception\SingleImportDataException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SingleImportPromoCodeValidator
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
     * @param array $data
     */
    public function validate(array $data): void
    {
        $violations = $this->validator->validate($data, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'origin' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255]),
                ],
                'code' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255]),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            throw new SingleImportDataException($violations);
        }
    }

    /**
     * @param array $data
     */
    public function validateOptions(array $data): void
    {
        $violations = $this->validator->validate($data, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'file' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 255]),
                ],
                'price_limit' => [
                    new Assert\Type(['type' => 'integer']),
                ],
                'discount_type' => [
                    new Assert\Type(['type' => 'integer']),
                ],
                'discount' => [
                    new Assert\Type(['type' => 'integer']),
                ],
                'moeve_code' => [
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 2]),
                ],
                'on_sale' => [
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 3]),
                ],
                'in_action' => [
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 3]),
                ],
                'sconto_price' => [
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 3]),
                ],
                'without_low_price' => [
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 3]),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            throw new SingleImportDataException($violations);
        }
    }
}
