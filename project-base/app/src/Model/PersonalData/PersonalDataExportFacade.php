<?php

declare(strict_types=1);

namespace App\Model\PersonalData;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory;
use Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonalDataExportFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    private DomainRouterFactory $domainRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory
     */
    private PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade
     */
    private PersonalDataAccessRequestFacade $personalDataAccessRequestFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory
     * @param \Shopsys\FrameworkBundle\Model\PersonalData\PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
     */
    public function __construct(
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        PersonalDataAccessRequestDataFactory $personalDataAccessRequestDataFactory,
        PersonalDataAccessRequestFacade $personalDataAccessRequestFacade
    ) {
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->personalDataAccessRequestDataFactory = $personalDataAccessRequestDataFactory;
        $this->personalDataAccessRequestFacade = $personalDataAccessRequestFacade;
    }

    /**
     * @param string $email
     * @return string
     */
    public function generateExportRequestAndGetLink(string $email): string
    {
        $personalDataAccessRequestData = $this->personalDataAccessRequestDataFactory->createForExport();
        $personalDataAccessRequestData->email = $email;
        $exportHash = $this->personalDataAccessRequestFacade->createPersonalDataAccessRequest(
            $personalDataAccessRequestData,
            $this->domain->getId()
        )->getHash();

        return $this->getPersonalDataExportLink($exportHash);
    }

    /**
     * @param string $exportHash
     * @return string
     */
    public function getPersonalDataExportLink(string $exportHash): string
    {
        $router = $this->domainRouterFactory->getRouter($this->domain->getId());

        $routeParameters = [
            'hash' => $exportHash,
        ];

        return $router->generate(
            'front_export_personal_data',
            $routeParameters,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
