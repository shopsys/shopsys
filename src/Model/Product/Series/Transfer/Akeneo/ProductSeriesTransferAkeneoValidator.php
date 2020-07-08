<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Transfer\Akeneo;

use App\Component\Akeneo\AkeneoHelper;
use App\Model\Product\Series\Transfer\Akeneo\Exception\MissingExpectedLocaleException;
use App\Model\Product\Series\Transfer\Akeneo\Exception\MissingRequiredAttributeException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductSeriesTransferAkeneoValidator
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
     * @param array $akeneoData
     */
    public function validate(array $akeneoData): void
    {
        $violations = $this->validator->validate($akeneoData, new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'code' => [
                    new Assert\NotBlank(),
                    new Assert\Type(['type' => 'string']),
                    new Assert\Length(['max' => 100]),
                ],
            ],
        ]));

        $this->validateLocalizedData($violations, $akeneoData['values'] ?? null, 'label', [
            new Assert\Type(['type' => 'string']),
            new Assert\Length(['max' => 255]),
        ]);
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
            throw new MissingRequiredAttributeException($validateKeyName);
        }

        $subExpectedAndRealLocales = $this->getSubExpectedAndRealLocales($data[$validateKeyName]);
        if (count($subExpectedAndRealLocales) > 0) {
            throw new MissingExpectedLocaleException($validateKeyName, implode(', ', $subExpectedAndRealLocales));
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
     * @param array $data
     * @return array
     */
    private function getSubExpectedAndRealLocales(array $data): array
    {
        $expectedLocales = AkeneoHelper::ESHOP_LOCALES_BY_AKENEO_LOCALES;
        foreach ($data as $localeRow) {
            if (array_key_exists($localeRow['locale'], $expectedLocales)) {
                unset($expectedLocales[$localeRow['locale']]);
            }
        }
        return $expectedLocales;
    }
}
