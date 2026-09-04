<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\GiftVoucher;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucher;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherDataFactory;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherGenerationFacade;
use Shopsys\FrameworkBundle\Model\GiftVoucher\GiftVoucherPdfGenerator;
use Tests\App\Test\FunctionalTestCase;

final class GiftVoucherPdfGeneratorTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private GiftVoucherPdfGenerator $giftVoucherPdfGenerator;

    /**
     * @inject
     */
    private GiftVoucherDataFactory $giftVoucherDataFactory;

    public function testGeneratedPdfIsValidPdfDocument(): void
    {
        $giftVoucherData = $this->giftVoucherDataFactory->create();
        $giftVoucherData->code = 'ACDEFHJKMNPR';
        $giftVoucherData->domainId = Domain::FIRST_DOMAIN_ID;
        $giftVoucherData->valueWithVat = Money::create(1000);
        $giftVoucherData->currencyCode = 'CZK';
        $giftVoucherData->activatedAt = new DateTimeImmutable();
        $giftVoucherData->validUntil = new DateTimeImmutable(GiftVoucherGenerationFacade::VALIDITY_MODIFIER);
        $giftVoucher = new GiftVoucher($giftVoucherData);

        $pdfContent = $this->giftVoucherPdfGenerator->generatePdfContent($giftVoucher);

        $this->assertStringStartsWith('%PDF', $pdfContent);
    }
}
