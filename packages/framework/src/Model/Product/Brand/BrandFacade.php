<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

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

    public function __construct(
        EntityManagerInterface $em,
        BrandRepository $brandRepository,
        ImageFacade $imageFacade,
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain,
        BrandFactoryInterface $brandFactory
    ) {
        $this->em = $em;
        $this->brandRepository = $brandRepository;
        $this->imageFacade = $imageFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
        $this->brandFactory = $brandFactory;
    }
    
    public function getById(int $brandId): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        return $this->brandRepository->getById($brandId);
    }

    public function create(BrandData $brandData): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        $domains = $this->domain->getAll();
        $brand = $this->brandFactory->create($brandData);
        $this->em->persist($brand);
        $this->em->flush();
        $this->imageFacade->uploadImage($brand, $brandData->image->uploadedFiles, null);

        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_brand_detail',
                $brand->getId(),
                $brand->getName(),
                $domain->getId()
            );
        }
        $this->em->flush();

        return $brand;
    }

    public function edit($brandId, BrandData $brandData): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        $domains = $this->domain->getAll();
        $brand = $this->brandRepository->getById($brandId);
        $brand->edit($brandData);
        $this->imageFacade->uploadImage($brand, $brandData->image->uploadedFiles, null);
        $this->em->flush();

        $this->friendlyUrlFacade->saveUrlListFormData('front_brand_detail', $brand->getId(), $brandData->urls);
        foreach ($domains as $domain) {
            $this->friendlyUrlFacade->createFriendlyUrlForDomain(
                'front_brand_detail',
                $brand->getId(),
                $brand->getName(),
                $domain->getId()
            );
        }
        $this->em->flush();

        return $brand;
    }
    
    public function deleteById(int $brandId): void
    {
        $brand = $this->brandRepository->getById($brandId);
        $this->em->remove($brand);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAll(): array
    {
        return $this->brandRepository->getAll();
    }
}
