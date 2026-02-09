<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog\Mail;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Mailer\MailerHelper;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Image\ProductImageFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Watchdog\Watchdog;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WatchdogMail
{
    public const string WATCHDOG_MAIL_TEMPLATE_NAME = 'watchdog_mail';
    public const string VARIABLE_PRODUCT_NAME = '{product_name}';
    public const string VARIABLE_PRODUCT_QUANTITY = '{product_quantity}';
    public const string VARIABLE_PRODUCT_URL = '{product_url}';
    public const string VARIABLE_PRODUCT_IMAGE = '{product_image}';

    public function __construct(
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly ProductImageFacade $productImageFacade,
        protected readonly MailerHelper $mailerHelper,
    ) {
    }

    public function createMessage(MailTemplate $template, Watchdog $watchdog): MessageData
    {
        return $this->createMessageFromProductAndEmail(
            $template,
            $watchdog->getEmail(),
            $watchdog->getProduct(),
            $watchdog->getDomainId(),
        );
    }

    public function createMessageFromProductAndEmail(
        MailTemplate $template,
        string $email,
        Product $product,
        int $domainId,
    ): MessageData {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        return new MessageData(
            $email,
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $domainId),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $domainId),
            $this->getBodyVariablesReplacements($product, $domainId),
            $this->getSubjectVariablesReplacements($product, $locale),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function getSubjectVariablesReplacements(Product $product, string $locale): array
    {
        return [
            self::VARIABLE_PRODUCT_NAME => $this->mailerHelper->escapeOptionalString($product->getName($locale)),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getBodyVariablesReplacements(Product $product, int $domainId): array
    {
        $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

        return [
            ...$this->getSubjectVariablesReplacements($product, $locale),
            self::VARIABLE_PRODUCT_URL => $this->getProductUrl($product, $domainId),
            self::VARIABLE_PRODUCT_IMAGE => $this->productImageFacade->getProductImageUrl($product, $domainId),
            self::VARIABLE_PRODUCT_QUANTITY => $this->getProductQuantity($product, $domainId, $locale),
        ];
    }

    protected function getProductQuantity(Product $product, int $domainId, string $locale): string
    {
        $productQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId(
            $product,
            $domainId,
        );

        $unit = $product->getUnit()->getName($locale);

        if ($productQuantity === null) {
            return '';
        }

        return $productQuantity . ' ' . $unit;
    }

    protected function getProductUrl(Product $product, int $domainId): string
    {
        return $this->domainRouterFactory->getRouter($domainId)->generate(
            'front_product_detail',
            ['id' => $product->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
