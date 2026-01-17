<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PersonalData;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonalDataExportFacade
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory,
        protected readonly PersonalDataAccessRequestFacade $personalDataAccessRequestFacade,
    ) {
    }

    public function generateExportRequestAndGetLink(string $email): string
    {
        $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForExport();
        $personalDataAccessRequestData->email = $email;
        $exportHash = $this->personalDataAccessRequestFacade->createPersonalDataAccessRequest(
            $personalDataAccessRequestData,
            $this->domain->getId(),
        )->getHash();

        return $this->getPersonalDataExportLink($exportHash);
    }

    public function getPersonalDataExportLink(string $exportHash): string
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        $routeParameters = [
            'hash' => $exportHash,
        ];

        return $router->generate(
            'front_export_personal_data',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
    }
}
