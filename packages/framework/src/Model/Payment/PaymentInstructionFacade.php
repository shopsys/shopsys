<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Mail\MailEmbedCollector;
use Shopsys\FrameworkBundle\Model\Mail\MailEmbedData;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Twig\Environment;

class PaymentInstructionFacade
{
    protected const string PLACEHOLDER_QR_CODE = '{qr_code}';

    public function __construct(
        protected readonly Domain $domain,
        protected readonly Environment $twig,
        protected readonly PngWriter $pngWriter,
        protected readonly MailEmbedCollector $mailEmbedCollector,
        protected readonly SpaydValidator $spaydValidator,
        protected readonly LoggerInterface $logger,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    public function getPaymentInstructionsForEmail(Order $order): ?string
    {
        return $this->getPaymentInstructions($order, true);
    }

    public function getPaymentInstructionsForOrderSubmittedPage(Order $order): ?string
    {
        return $this->getPaymentInstructions($order, false, false);
    }

    protected function getPaymentInstructions(Order $order, bool $forEmail, bool $inOrderLocale = true): ?string
    {
        $locale = $inOrderLocale ? $this->domain->getDomainConfigById($order->getDomainId())->getLocale() : $this->domain->getLocale();
        $paymentInstructions = $order->getPaymentItem()->getPayment()->getInstructions($locale);

        if ($paymentInstructions === null) {
            return null;
        }

        $variablesKeysAndValues = [
            self::PLACEHOLDER_QR_CODE => $this->getQrCode($order, $forEmail, $locale),
        ];

        return strtr($paymentInstructions, $variablesKeysAndValues);
    }

    protected function getQrCode(Order $order, bool $forEmail, string $locale): string
    {
        if (!$this->customerUserRoleResolver->canCustomerUserSeePrices($order->getCustomerUser())) {
            return '';
        }

        $payment = $order->getPaymentItem()->getPayment();

        $accountNumber = $payment->getAccountNumber($order->getDomainId());

        if (
            $accountNumber === null
            || $payment->getType() !== PaymentTypeEnum::TYPE_BANK_TRANSFER
        ) {
            return '';
        }

        try {
            $spdString = $this->createSpdString(
                $payment,
                $order->getNumber(),
                (float)$order->getTotalPrice()->getPriceWithVat()->getAmount(),
                $order->getCurrency(),
                $order->getDomainId(),
            );

            $this->spaydValidator->validate($spdString);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error('There was some invalid SPD data. QR code was not generated.', [
                'exception' => $exception,
            ]);

            return '';
        }

        $qrCodeBase64Data = $this->getQrCodeBase64Data($spdString);
        $qrCodeImageSrc = $qrCodeBase64Data;

        if ($forEmail) {
            $mainEmbedData = new MailEmbedData($qrCodeBase64Data, 'qrCode.png', 'image/png');
            $this->mailEmbedCollector->addEmbed($mainEmbedData);
            $qrCodeImageSrc = 'cid:' . $mainEmbedData->fileName;
        }

        return $this->twig->render('@ShopsysFramework/Front/Content/Payment/qrCode.html.twig', [
            'orderNumber' => $order->getNumber(),
            'totalPrice' => $order->getTotalPrice(),
            'accountNumber' => $accountNumber,
            'qrCodeImageSrc' => $qrCodeImageSrc,
            'order' => $order,
            'locale' => $locale,
            'iban' => $payment->getIban($order->getDomainId()),
            'bicSwift' => $payment->getBicSwift($order->getDomainId()),
        ]);
    }

    protected function getQrCodeBase64Data(string $spdString): string
    {
        $qrCode = new QrCode($spdString);
        $result = $this->pngWriter->write($qrCode);

        return $result->getDataUri();
    }

    /**
     * @return array<string,string>
     */
    public function getInstructionsPlaceholders(): array
    {
        return [
            self::PLACEHOLDER_QR_CODE => t('QR code'),
        ];
    }

    protected function createSpdString(
        Payment $payment,
        string $variableSymbol,
        float $amount,
        Currency $currency,
        int $domainId,
    ): string {
        return SpaydHelper::createSpayd(
            $payment->getIban($domainId),
            $amount,
            $currency->getCode(),
            $currency->getCode() === Currency::CODE_CZK ? null : $payment->getBicSwift($domainId),  // BIC required for non-CZK
            $variableSymbol,
        );
    }
}
