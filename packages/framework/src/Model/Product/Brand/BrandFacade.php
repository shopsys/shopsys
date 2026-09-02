<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BrandFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BrandRepository $brandRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
        protected readonly BrandFactory $brandFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function getById(int $brandId): Brand
    {
        return $this->brandRepository->getById($brandId);
    }

    public function create(BrandData $brandData): Brand
    {
        $domains = $this->domain->getAll();
        $brand = $this->brandFactory->create($brandData);
        $this->em->persist($brand);
        $this->em->flush();
        $this->imageFacade->manageImages($brand, $brandData->image);

        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_brand_detail',
                $brand->getId(),
                $brand->getName(),
                $domain->getId(),
            );
        }
        $this->em->flush();

        $this->dispatchBrandEvent($brand, BrandEvent::CREATE);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BRANDS_QUERY_KEY_PART);

        return $brand;
    }

    public function edit(int $brandId, BrandData $brandData): Brand
    {
        $domains = $this->domain->getAll();
        $brand = $this->brandRepository->getById($brandId);

        $brand->edit($brandData);
        $this->imageFacade->manageImages($brand, $brandData->image);
        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_brand_detail', $brand->getId(), $brandData->urls);

        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_brand_detail',
                $brand->getId(),
                $brand->getName(),
                $domain->getId(),
            );
        }
        $this->em->flush();

        $this->dispatchBrandEvent($brand, BrandEvent::UPDATE);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BRANDS_QUERY_KEY_PART);

        return $brand;
    }

    public function deleteById(int $brandId): void
    {
        $brand = $this->brandRepository->getById($brandId);
        $this->em->remove($brand);
        $this->dispatchBrandEvent($brand, BrandEvent::DELETE);

        $this->em->flush();

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::BRANDS_QUERY_KEY_PART);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAll(): array
    {
        return $this->brandRepository->getAll();
    }

    /**
     * @see \Shopsys\FrameworkBundle\Model\Product\Brand\BrandEvent class
     */
    protected function dispatchBrandEvent(Brand $brand, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new BrandEvent($brand), $eventType);
    }

    public function getByUuid(string $uuid): Brand
    {
        return $this->brandRepository->getOneByUuid($uuid);
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getByUuids(array $uuids): array
    {
        return $this->brandRepository->getByUuids($uuids);
    }

    /**
     * @param int[] $brandsIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsByIds(array $brandsIds): array
    {
        return $this->brandRepository->getBrandsByIds($brandsIds);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsBySearchText(string $searchText): array
    {
        return $this->brandRepository->getBrandsBySearchText($searchText);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAllWithDomainsAndTranslations(DomainConfig $domainConfig): array
    {
        return $this->brandRepository->getAllWithDomainsAndTranslations($domainConfig);
    }

    /**
     * @param int[] $brandIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null>
     */
    public function getByIds(array $brandIds, DomainConfig $domainConfig): array
    {
        return $this->brandRepository->getByIds($brandIds, $domainConfig);
    }
}
