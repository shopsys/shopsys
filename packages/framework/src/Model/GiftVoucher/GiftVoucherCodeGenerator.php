<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use Shopsys\FrameworkBundle\Model\GiftVoucher\Exception\UniqueGiftVoucherCodeGenerationFailedException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeRepository;

class GiftVoucherCodeGenerator
{
    public const string CODE_ALPHABET = '23456789ACDEFHJKMNPRTWXY';

    public const int CODE_LENGTH = 12;

    protected const int MAX_UNIQUE_CODE_GENERATION_ATTEMPTS = 100;

    public function __construct(
        protected readonly GiftVoucherRepository $giftVoucherRepository,
        protected readonly PromoCodeRepository $promoCodeRepository,
    ) {
    }

    public function generateUniqueCode(): string
    {
        for ($attempt = 0; $attempt < static::MAX_UNIQUE_CODE_GENERATION_ATTEMPTS; $attempt++) {
            $code = $this->generateCode();

            if ($this->isCodeUnique($code)) {
                return $code;
            }
        }

        throw new UniqueGiftVoucherCodeGenerationFailedException();
    }

    protected function generateCode(): string
    {
        $alphabetMaxIndex = strlen(static::CODE_ALPHABET) - 1;
        $code = '';

        for ($i = 0; $i < static::CODE_LENGTH; $i++) {
            $code .= static::CODE_ALPHABET[random_int(0, $alphabetMaxIndex)];
        }

        return $code;
    }

    protected function isCodeUnique(string $code): bool
    {
        return !$this->giftVoucherRepository->existsByCode($code)
            && !$this->promoCodeRepository->existsByCode($code);
    }

    public function normalizeCode(string $code): string
    {
        return strtoupper((string)preg_replace('/[\s-]+/', '', $code));
    }
}
