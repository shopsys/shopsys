<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;

class OrganizationFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrganizationRepository $organizationRepository,
        protected readonly OrganizationFactory $organizationFactory,
        protected readonly ImageFacade $imageFacade,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function findByDomainId(int $domainId): ?Organization
    {
        return $this->organizationRepository->findByDomainId($domainId);
    }

    public function edit(int $domainId, OrganizationData $organizationData): Organization
    {
        $organization = $this->findByDomainId($domainId);

        if ($organization === null) {
            $organization = $this->organizationFactory->create($domainId, $organizationData);
            $this->em->persist($organization);
        } else {
            $organization->edit($organizationData);
        }

        $this->em->flush();
        $this->imageFacade->manageImages($organization, $organizationData->image);
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);

        return $organization;
    }
}
