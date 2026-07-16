<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Shopsys\FrameworkBundle\Component\HttpFoundation\DownloadFileResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Model\GiftVoucher\Exception\GiftVoucherNotFoundException;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDownloadHashProvider;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherPdfGenerator;

class GiftVoucherController
{
    public function __construct(
        protected readonly GiftVoucherFacade $giftVoucherFacade,
        protected readonly GiftVoucherPdfGenerator $giftVoucherPdfGenerator,
        protected readonly GiftVoucherDownloadHashProvider $giftVoucherDownloadHashProvider,
    ) {
    }

    public function downloadPdfAction(string $uuid, string $hash): DownloadFileResponse
    {
        try {
            $giftVoucher = $this->giftVoucherFacade->getByUuid($uuid);
        } catch (GiftVoucherNotFoundException $exception) {
            throw new NotFoundRedirectToStorefrontException(
                sprintf('Gift voucher with UUID "%s" not found.', $uuid),
                $exception,
            );
        }

        if (!$this->giftVoucherDownloadHashProvider->isHashValid($giftVoucher, $hash)) {
            throw new NotFoundRedirectToStorefrontException(
                sprintf('Gift voucher with UUID "%s" not found.', $uuid),
            );
        }

        $pdfContent = $this->giftVoucherPdfGenerator->generatePdfContent($giftVoucher);

        return new DownloadFileResponse(
            sprintf('gift-voucher-%s.pdf', $giftVoucher->getCode()),
            $pdfContent,
            'application/pdf',
        );
    }
}
