<?php

declare(strict_types=1);

namespace App\Component\Router\Import;

use App\Component\Akeneo\Transfer\Exception\SingleImportDataException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SingleImportUrlRedirectTableValidator
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
                'from' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 500]),
                ],
                'to' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 500]),
                ],
            ],
        ]));

        if (count($violations) > 0) {
            throw new SingleImportDataException($violations);
        }
    }

    /**
     * @param mixed $data
     */
    public function validateFile($data): void
    {
        $violations = $this->validator->validate($data, [
            new Assert\NotBlank(),
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);

        if (count($violations) > 0) {
            throw new SingleImportDataException($violations);
        }
    }
}
