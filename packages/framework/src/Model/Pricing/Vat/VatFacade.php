<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatService
     */
    protected $vatService;

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

    public function __construct(
        EntityManagerInterface $em,
        VatRepository $vatRepository,
        VatService $vatService,
        Setting $setting,
        ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        VatFactoryInterface $vatFactory
    ) {
        $this->em = $em;
        $this->vatRepository = $vatRepository;
        $this->vatService = $vatService;
        $this->setting = $setting;
        $this->productPriceRecalculationScheduler = $productPriceRecalculationScheduler;
        $this->vatFactory = $vatFactory;
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
    public function getAll()
    {
        return $this->vatRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllIncludingMarkedForDeletion()
    {
        return $this->vatRepository->getAllIncludingMarkedForDeletion();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function create(VatData $vatData)
    {
        $vat = $this->vatFactory->create($vatData);
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

        $this->productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();

        return $vat;
    }

    /**
     * @param int $vatId
     * @param int|null $newVatId
     */
    public function deleteById($vatId, $newVatId = null)
    {
        $oldVat = $this->vatRepository->getById($vatId);
        $newVat = $newVatId ? $this->vatRepository->getById($newVatId) : null;

        if ($oldVat->isMarkedAsDeleted()) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatMarkedAsDeletedDeleteException();
        }

        if ($this->vatRepository->existsVatToBeReplacedWith($oldVat)) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatWithReplacedDeleteException();
        }

        if ($newVat !== null) {
            $newDefaultVat = $this->vatService->getNewDefaultVat(
                $this->getDefaultVat(),
                $oldVat,
                $newVat
            );
            $this->setDefaultVat($newDefaultVat);

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
        $this->em->flush($vatsForDelete);

        return count($vatsForDelete);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getDefaultVat()
    {
        $defaultVatId = $this->setting->get(Vat::SETTING_DEFAULT_VAT);

        return $this->vatRepository->getById($defaultVatId);
    }

    public function setDefaultVat(Vat $vat)
    {
        $this->setting->set(Vat::SETTING_DEFAULT_VAT, $vat->getId());
    }

    /**
     * @return bool
     */
    public function isVatUsed(Vat $vat)
    {
        $defaultVat = $this->getDefaultVat();

        return $defaultVat === $vat || $this->vatRepository->isVatUsed($vat);
    }

    /**
     * @param int $vatId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getAllExceptId($vatId)
    {
        return $this->vatRepository->getAllExceptId($vatId);
    }
}
