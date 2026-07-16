<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\GiftVoucher;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDownloadHashProvider;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PurchasedGiftVouchersQuery extends AbstractQuery
{
    public function __construct(
        protected readonly GiftVoucherRepository $giftVoucherRepository,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly GiftVoucherDownloadHashProvider $giftVoucherDownloadHashProvider,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher[]
     */
    public function purchasedGiftVouchersByOrderQuery(Order $order): array
    {
        return $this->giftVoucherRepository->getAllCreatedOnOrder($order);
    }

    public function giftVoucherPdfUrlQuery(GiftVoucher $giftVoucher): string
    {
        return $this->domainRouterFactory->getRouter($giftVoucher->getDomainId())->generate(
            'front_gift_voucher_download',
            [
                'uuid' => $giftVoucher->getUuid(),
                'hash' => $this->giftVoucherDownloadHashProvider->getHash($giftVoucher),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
