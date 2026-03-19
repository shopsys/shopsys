<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PhonePrefix;

use libphonenumber;

final class PhoneData
{
    public function __construct(
        public ?string $countryCode = null,
        public ?string $prefix = null,
        public ?string $number = null,
    ) {
    }

    public function toPhoneNumber(): ?string
    {
        if ($this->prefix === null && $this->number === null) {
            return null;
        }

        return trim(($this->prefix ?? '') . ($this->number ?? ''));
    }

    /**
     * @param array{countryCode: string, prefix: string, number: string} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            countryCode: $data['countryCode'],
            prefix: $data['prefix'],
            number: $data['number'],
        );
    }

    public function toLibPhoneNumberObject(): libphonenumber\PhoneNumber
    {
        $libPhoneNumber = new libphonenumber\PhoneNumber()
            ->setCountryCode((int)($this->prefix ? ltrim($this->prefix, '+') : 0));

        if ($this->number !== null) {
            $libPhoneNumber->setNationalNumber($this->number);
        }

        return $libPhoneNumber;
    }
}
