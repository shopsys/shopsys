<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

class GiftVoucherDownloadHashProvider
{
    public function __construct(
        protected readonly string $secret,
    ) {
    }

    public function getHash(GiftVoucher $giftVoucher): string
    {
        return hash_hmac(
            'sha256',
            sprintf('giftVoucherDownload:%s:%s', $giftVoucher->getUuid(), $giftVoucher->getCode()),
            $this->secret,
        );
    }

    public function isHashValid(GiftVoucher $giftVoucher, string $hash): bool
    {
        return hash_equals($this->getHash($giftVoucher), $hash);
    }
}
