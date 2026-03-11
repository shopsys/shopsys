<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PhonePrefix;

use Override;
use Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode;
use Symfony\Component\Form\DataTransformerInterface;

class CountryDialCodeTransformer implements DataTransformerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[] $allCountryDialCodes
     */
    public function __construct(
        private readonly array $allCountryDialCodes,
        private readonly bool $multiple = false,
    ) {
    }

    /**
     * Transforms string code(s) from the data class into CountryDialCode object(s) for the form field.
     *
     * @param string[]|string|null $value
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]|\Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode|null
     */
    #[Override]
    public function transform(mixed $value): mixed
    {
        $codeMap = [];

        foreach ($this->allCountryDialCodes as $countryDialCode) {
            $codeMap[$countryDialCode->code] = $countryDialCode;
        }

        if ($this->multiple) {
            return array_map(static fn (string $code): ?CountryDialCode => $codeMap[$code] ?? null, (array)$value)
                    |> array_filter(...)
                    |> array_values(...);
        }

        if ($value === null) {
            return null;
        }

        return $codeMap[$value] ?? null;
    }

    /**
     * Transforms CountryDialCode object(s) from the form field back into string code(s) for the data class.
     *
     * @param \Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode[]|\Shopsys\FrameworkBundle\Model\PhonePrefix\CountryDialCode|null $value
     * @return string|string[]|null
     */
    #[Override]
    public function reverseTransform(mixed $value): string|array|null
    {
        if ($this->multiple) {
            return array_map(
                static fn (CountryDialCode $countryDialCode): string => $countryDialCode->code,
                (array)$value,
            );
        }

        if (!$value instanceof CountryDialCode) {
            return null;
        }

        return $value->code;
    }
}
