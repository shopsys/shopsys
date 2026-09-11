<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Model\PriceList;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\Handler\CrudHandlerInterface;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListData;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Webmozart\Assert\Assert;

class PriceListCrudHandler implements CrudHandlerInterface
{
    /**
     * Namespace of the quick domain filter of the price list CRUD list, see AbstractCrudController::getListDomainFilterNamespace()
     */
    public const string LIST_DOMAIN_FILTER_NAMESPACE = 'crud_price_list';

    public function __construct(
        protected readonly PriceListFacade $priceListFacade,
        protected readonly PriceListDataFactory $priceListDataFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getById(int $id): Presentable
    {
        return $this->priceListFacade->getById($id);
    }

    /**
     * {@inheritdoc}
     *
     * A new price list is preset to the domain selected in the list quick filter
     */
    #[Override]
    public function createData(): object
    {
        $priceListData = $this->priceListDataFactory->create();
        $priceListData->domainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId(
            static::LIST_DOMAIN_FILTER_NAMESPACE,
            $this->domain->getAdminEnabledDomainIds(),
        ) ?? Domain::FIRST_DOMAIN_ID;

        return $priceListData;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function create(object $data): Presentable
    {
        Assert::isInstanceOf($data, PriceListData::class);

        return $this->priceListFacade->create($data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createDataFromEntity(object $entity): object
    {
        Assert::isInstanceOf($entity, PriceList::class);

        return $this->priceListDataFactory->createFromPriceList($entity);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function edit(object $entity, object $data): void
    {
        Assert::isInstanceOf($entity, PriceList::class);
        Assert::isInstanceOf($data, PriceListData::class);

        $this->priceListFacade->edit($entity->getId(), $data);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function delete(object $entity): void
    {
        Assert::isInstanceOf($entity, PriceList::class);

        $this->priceListFacade->delete($entity->getId());
    }
}
