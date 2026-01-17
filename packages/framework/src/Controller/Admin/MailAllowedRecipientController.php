<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailAllowedRecipientController extends AbstractController
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly MailerSettingProvider $mailerSettingProvider,
    ) {
    }

    #[Route('/mail-allowed-recipient/list/')]
    #[RequireRole([SystemRole::ADMIN])]
    public function listAction(): Response
    {
        $patternsByDomainId = [];
        $enabledWhitelistByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $patternsByDomainId[$domainId] = $this->mailerSettingProvider->getWhitelistPatternsAsArray($domainId);
            $enabledWhitelistByDomainId[$domainId] = $this->mailerSettingProvider->isWhitelistEnabled($domainId);
        }

        return $this->render('@ShopsysAdministration/content/mailAllowedRecipient/list.html.twig', [
            'patternsByDomainId' => $patternsByDomainId,
            'enabledWhitelistByDomainId' => $enabledWhitelistByDomainId,
            'isDeliveryDisabled' => $this->mailerSettingProvider->isDeliveryDisabled(),
        ]);
    }
}
