<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BrandFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository $brandRepository
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFactoryInterface $brandFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly BrandRepository $brandRepository,
        protected readonly ImageFacade $imageFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly Domain $domain,
        protected readonly BrandFactoryInterface $brandFactory,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getById($brandId)
    {
        return $this->brandRepository->getById($brandId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function create(BrandData $brandData)
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

        return $brand;
    }

    /**
     * @param int $brandId
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function edit($brandId, BrandData $brandData)
    {
        $domains = $this->domain->getAll();
        $brand = $this->brandRepository->getById($brandId);
        $originalName = $brand->getName();

        $brand->edit($brandData);
        $this->imageFacade->manageImages($brand, $brandData->image);
        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_brand_detail', $brand->getId(), $brandData->urls);

        if ($originalName !== $brand->getName()) {
            foreach ($domains as $domain) {
                $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                    'front_brand_detail',
                    $brand->getId(),
                    $brand->getName(),
                    $domain->getId(),
                );
            }
        }
        $this->em->flush();

        $this->dispatchBrandEvent($brand, BrandEvent::UPDATE);

        return $brand;
    }

    /**
     * @param int $brandId
     */
    public function deleteById(int $brandId): void
    {
        $brand = $this->brandRepository->getById($brandId);
        $this->em->remove($brand);
        $this->dispatchBrandEvent($brand, BrandEvent::DELETE);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAll()
    {
        return $this->brandRepository->getAll();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param string $eventType
     * @see \Shopsys\FrameworkBundle\Model\Product\Brand\BrandEvent class
     */
    protected function dispatchBrandEvent(Brand $brand, string $eventType): void
    {
        $this->eventDispatcher->dispatch(new BrandEvent($brand), $eventType);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
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
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsBySearchText(string $searchText): array
    {
        return $this->brandRepository->getBrandsBySearchText($searchText);
    }
}
