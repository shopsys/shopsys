<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class OrganizationSettingFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrganizationFactory $organizationFactory,
        protected readonly OrganizationDataFactory $organizationDataFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly Domain $domain,
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function findByDomainId(int $domainId): ?Organization
    {
        return $this->em->getRepository(Organization::class)->findOneBy(['domainId' => $domainId]);
    }

    public function getSettings(int $domainId): OrganizationData
    {
        $organization = $this->findByDomainId($domainId);

        return $organization === null
            ? $this->organizationDataFactory->create()
            : $this->organizationDataFactory->createFromOrganization($organization);
    }

    public function saveSettings(OrganizationData $data, int $domainId): void
    {
        $organization = $this->findByDomainId($domainId);

        if ($organization === null) {
            $organization = $this->organizationFactory->create($domainId, $data);
            $this->em->persist($organization);
        } else {
            $organization->edit($data);
        }

        $this->em->flush();
        $this->imageFacade->manageImages($organization, $data->image);
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);
    }

    public function getOrganization(int $domainId): OrganizationData
    {
        $data = $this->getSettings($domainId);
        $organization = $this->findByDomainId($domainId);

        if ($organization !== null) {
            try {
                $data->logo = $this->imageFacade->getImageUrl($this->domain->getDomainConfigById($domainId), $organization);
            } catch (ImageNotFoundException) {
                $data->logo = null;
            }
        }

        $data->socialNetworkUrls = array_values(array_filter($this->mailSettingFacade->getFooterIconUrls($domainId)));

        return $data;
    }
}
