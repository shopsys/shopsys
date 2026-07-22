<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatMarkedAsDeletedDeleteException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatWithReplacedDeleteException;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;

class VatFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly VatRepository $vatRepository,
        protected readonly VatSetting $vatSetting,
        protected readonly VatFactory $vatFactory,
        protected readonly Domain $domain,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    public function getById(int $vatId): Vat
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

    public function edit(int $vatId, VatData $vatData): Vat
    {
        $vat = $this->vatRepository->getById($vatId);
        $vat->edit($vatData);
        $this->em->flush();

        $this->productRecalculationDispatcher->dispatchAllProducts();

        return $vat;
    }

    public function deleteById(int $vatId, ?int $newVatId = null): void
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

    public function replaceVatForVatsMarkedForDeletion(): void
    {
        $this->vatRepository->replaceVatForVatsMarkedForDeletion();
    }

    public function deleteAllReplacedVats(): int
    {
        $vatsForDelete = $this->vatRepository->getVatsMarkedForDeletionWithoutReferences();

        foreach ($vatsForDelete as $vatForDelete) {
            $this->em->remove($vatForDelete);
        }
        $this->em->flush();

        return count($vatsForDelete);
    }

    public function getDefaultVatForDomain(int $domainId): Vat
    {
        $defaultVatId = $this->vatSetting->getDefaultVatId($domainId);

        return $this->vatRepository->getById($defaultVatId);
    }

    public function setDefaultVatForDomain(Vat $vat, int $domainId): void
    {
        $this->vatSetting->setDefaultVatId($vat->getId(), $domainId);
    }

    public function isVatUsed(Vat $vat): bool
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
