<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider;
use Shopsys\FrameworkBundle\Model\Security\AccessControl\AccessControlRule;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MailAllowedRecipientController extends AbstractController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailerSettingProvider $mailerSettingProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly MailerSettingProvider $mailerSettingProvider,
    ) {
    }

    #[Route('/mail-allowed-recipient/list/')]
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[AccessControlRule([Roles::ROLE_ADMIN])]
    public function listAction(): Response
    {
        $patternsByDomainId = [];
        $enabledWhitelistByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $patternsByDomainId[$domainId] = $this->mailerSettingProvider->getWhitelistPatternsAsArray($domainId);
            $enabledWhitelistByDomainId[$domainId] = $this->mailerSettingProvider->isWhitelistEnabled($domainId);
        }

        return $this->render('@ShopsysFramework/Admin/Content/MailAllowedRecipient/list.html.twig', [
            'patternsByDomainId' => $patternsByDomainId,
            'enabledWhitelistByDomainId' => $enabledWhitelistByDomainId,
            'isDeliveryDisabled' => $this->mailerSettingProvider->isDeliveryDisabled(),
        ]);
    }
}
