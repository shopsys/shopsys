<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail\MailTemplateSender;

use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Watchdog\Mail\WatchdogMail;
use Shopsys\FrameworkBundle\Model\Watchdog\Mail\WatchdogMailFacade;

class WatchdogMailTemplateSender implements MailTemplateSenderInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\Mail\WatchdogMailFacade $watchdogMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        protected readonly WatchdogMailFacade $watchdogMailFacade,
        protected readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getFormLabelForEntityIdentifier(): ?string
    {
        return t('Product ID');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @return bool
     */
    #[Override]
    public function supports(MailTemplate $mailTemplate): bool
    {
        return str_contains($mailTemplate->getName(), WatchdogMail::WATCHDOG_MAIL_TEMPLATE_NAME);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @param string $mailTo
     * @param int|null $entityId
     */
    #[Override]
    public function sendTemplate(MailTemplate $mailTemplate, string $mailTo, ?int $entityId): void
    {
        $product = $this->productRepository->getById($entityId);
        $this->watchdogMailFacade->sendMailTemplate($mailTemplate, $product, $mailTo);
    }
}
