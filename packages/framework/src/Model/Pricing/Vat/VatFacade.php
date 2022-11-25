<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatMarkedAsDeletedDeleteException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatWithReplacedDeleteException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;

class VatFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository
     */
    protected $vatRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler
     */
    protected $productPriceRecalculationScheduler;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactoryInterface
     */
    protected $vatFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository $vatRepository
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactoryInterface $vatFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        EntityManagerInterface $em,
        VatRepository $vatRepository,
        Setting $setting,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        VatFactoryInterface $vatFactory,
        Domain $domain
    ) {
        $this->em = $em;
        $this->vatRepository = $vatRepository;
        $this->setting = $setting;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->vatFactory = $vatFactory;
        $this->domain = $domain;
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getById(int $vatId): Vat
    {
        return $this->vatRepository->getById($vatId);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomain(int $domainId): array
    {
        return $this->vatRepository->getAllForDomain($domainId);
    }

    /**
     * @param int $domainId
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomainExceptId(int $domainId, int $vatId): array
    {
        return $this->vatRepository->getAllForDomainExceptId($domainId, $vatId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function create(VatData $vatData, int $domainId): Vat
    {
        $vat = $this->vatFactory->create($vatData, $domainId);
        $this->em->persist($vat);
        $this->em->flush();

        return $vat;
    }

    /**
     * @param int $vatId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function edit(int $vatId, VatData $vatData): Vat
    {
        $vat = $this->vatRepository->getById($vatId);
        $vat->edit($vatData);
        $this->em->flush();

        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();

        return $vat;
    }

    /**
     * @param int $vatId
     * @param int|null $newVatId
     */
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

    /**
     * @return int
     */
    public function deleteAllReplacedVats(): int
    {
        $vatsForDelete = $this->vatRepository->getVatsWithoutProductsMarkedForDeletion();
        foreach ($vatsForDelete as $vatForDelete) {
            $this->em->remove($vatForDelete);
        }
        $this->em->flush();

        return count($vatsForDelete);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getDefaultVatForDomain(int $domainId): Vat
    {
        $defaultVatId = $this->setting->getForDomain(Vat::SETTING_DEFAULT_VAT, $domainId);

        return $this->vatRepository->getById($defaultVatId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $domainId
     */
    public function setDefaultVatForDomain(Vat $vat, int $domainId): void
    {
        $this->setting->setForDomain(Vat::SETTING_DEFAULT_VAT, $vat->getId(), $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @return bool
     */
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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllForDomainIncludingMarkedForDeletion(int $domainId): array
    {
        return $this->vatRepository->getAllForDomainIncludingMarkedForDeletion($domainId);
    }
}
