<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\AbstractGridInlineEdit;
use Shopsys\FrameworkBundle\Component\Grid\InlineEdit\Exception\InvalidFormDataException;
use Shopsys\FrameworkBundle\Form\Admin\Order\Status\OrderStatusFormType;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class OrderStatusInlineEdit extends AbstractGridInlineEdit
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\Grid\OrderStatusGridFactory $orderStatusGridFactory
     * @param \Symfony\Bundle\SecurityBundle\Security $security
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusDataFactory $orderStatusDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        OrderStatusGridFactory $orderStatusGridFactory,
        Security $security,
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly FormFactoryInterface $formFactory,
        protected readonly OrderStatusDataFactory $orderStatusDataFactory,
        protected readonly Domain $domain,
    ) {
        parent::__construct($orderStatusGridFactory, $security);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     * @return int|string
     */
    #[Override]
    protected function createEntityAndGetId(mixed $orderStatusData): int|string
    {
        if (!$this->domain->hasAdminAllDomainsEnabled()) {
            throw new InvalidFormDataException([
                t('Creating a record requires all domains to be enabled as domain-specific fields cannot be empty. If you want to proceed, select all domains in the Domain filter in the header first.'),
            ]);
        }

        $orderStatus = $this->orderStatusFacade->create($orderStatusData);

        return $orderStatus->getId();
    }

    /**
     * @param int|string $orderStatusId
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     */
    #[Override]
    protected function editEntity(int|string $orderStatusId, mixed $orderStatusData): void
    {
        $this->orderStatusFacade->edit($orderStatusId, $orderStatusData);
    }

    /**
     * @param int|string|null $rowId
     * @return \Symfony\Component\Form\FormInterface
     */
    #[Override]
    public function getForm(int|string|null $rowId): FormInterface
    {
        if ($rowId !== null) {
            $orderStatus = $this->orderStatusFacade->getById((int)$rowId);
            $orderStatusData = $this->orderStatusDataFactory->createFromOrderStatus($orderStatus);
        } else {
            $orderStatusData = $this->orderStatusDataFactory->create();
        }

        return $this->formFactory->create(OrderStatusFormType::class, $orderStatusData);
    }

    /**
     * @return string
     */
    #[Override]
    protected function getEditRole(): string
    {
        return Roles::ROLE_ORDER_STATUS_FULL;
    }
}
