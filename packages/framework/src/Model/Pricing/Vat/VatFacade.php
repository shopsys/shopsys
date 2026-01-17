<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatMarkedAsDeletedDeleteException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatWithReplacedDeleteException;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class VatFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly VatRepository $vatRepository,
        protected readonly Setting $setting,
        protected readonly VatFactory $vatFactory,
        protected readonly Domain $domain,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getById($vatId)
    {
        return $this->vatRepository->getById($vatId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomain(int $domainId): array
    {
        return $this->vatRepository->getAllForDomain($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomainExceptId(int $domainId, int $vatId): array
    {
        return $this->vatRepository->getAllForDomainExceptId($domainId, $vatId);
    }

    public function create(VatData $vatData, int $domainId): Vat
    {
        $vat = $this->vatFactory->create($vatData, $domainId);
        $this->em->persist($vat);
        $this->em->flush();

        return $vat;
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function edit($vatId, VatData $vatData)
    {
        $vat = $this->vatRepository->getById($vatId);
        $vat->edit($vatData);
        $this->em->flush();

        $this->productRecalculationDispatcher->dispatchAllProducts();

        return $vat;
    }

    /**
     * @param int $vatId
     * @param int|null $newVatId
     */
    public function deleteById($vatId, $newVatId = null): void
    {
        $oldVat = $this->vatRepository->getById($vatId);
        $newVat = $newVatId ? $this->vatRepository->getById($newVatId) : null;

        if ($oldVat->isMarkedAsDeleted()) {
            throw new VatMarkedAsDeletedDeleteException();
        }

        if ($this->vatRepository->existsVatToBeReplacedWith($oldVat)) {
            throw new VatWithReplacedDeleteException();
        }

        if ($newVat !== null) {
            $newDefaultVat = $this->getDefaultVatForDomain($oldVat->getDomainId());

            if ($newDefaultVat->getId() === $oldVat->getId()) {
                $newDefaultVat = $newVat;
            }

            $this->setDefaultVatForDomain($newDefaultVat, $oldVat->getDomainId());

            $this->vatRepository->replaceVat($oldVat, $newVat);
            $oldVat->markForDeletion($newVat);
        } else {
            $this->em->remove($oldVat);
        }

        $this->em->flush();
    }

    /**
     * @return int
     */
    public function deleteAllReplacedVats()
    {
        $vatsForDelete = $this->vatRepository->getVatsWithoutProductsMarkedForDeletion();

        foreach ($vatsForDelete as $vatForDelete) {
            $this->em->remove($vatForDelete);
        }
        $this->em->flush();

        return count($vatsForDelete);
    }

    public function getDefaultVatForDomain(int $domainId): Vat
    {
        $defaultVatId = $this->setting->getForDomain(Vat::SETTING_DEFAULT_VAT, $domainId);

        return $this->vatRepository->getById($defaultVatId);
    }

    public function setDefaultVatForDomain(Vat $vat, int $domainId): void
    {
        $this->setting->setForDomain(Vat::SETTING_DEFAULT_VAT, $vat->getId(), $domainId);
    }

    /**
     * @return bool
     */
    public function isVatUsed(Vat $vat)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $defaultVatForDomain = $this->getDefaultVatForDomain($domainId);

            if ($defaultVatForDomain === $vat) {
                return true;
            }
        }

        return $this->vatRepository->isVatUsed($vat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomainIncludingMarkedForDeletion(int $domainId): array
    {
        return $this->vatRepository->getAllForDomainIncludingMarkedForDeletion($domainId);
    }
}
