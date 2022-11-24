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
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository
     */
    protected $brandRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFactoryInterface
     */
    protected $brandFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

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
        EntityManagerInterface $em,
        BrandRepository $brandRepository,
        ImageFacade $imageFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain,
        BrandFactoryInterface $brandFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em = $em;
        $this->brandRepository = $brandRepository;
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
        $this->brandFactory = $brandFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getById(int $brandId): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        return $this->brandRepository->getById($brandId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandData $brandData
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function create(BrandData $brandData): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
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
                $domain->getId()
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
    public function edit(int $brandId, BrandData $brandData): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
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
                    $domain->getId()
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
        $this->friendlyUrlFacade->removeFriendlyUrlsForAllDomains('front_brand_detail', $brandId);

        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAll(): array
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
}
