<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Complaint;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Validator\InputValidator;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintItemDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\InvalidQuantityUserError;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\MissingComplaintItemsUserError;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\OrderItemNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\BaseTokenMutation;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\InvalidAccessUserError;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateComplaintMutation extends BaseTokenMutation
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository $complaintNumberSequenceRepository
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade $orderItemApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory $complaintItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintDataApiFactory $complaintDataApiFactory
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintItemDataApiFactory $complaintItemDataApiFactory
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly ComplaintNumberSequenceRepository $complaintNumberSequenceRepository,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly ComplaintItemFactory $complaintItemFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ComplaintDataApiFactory $complaintDataApiFactory,
        protected readonly ComplaintItemDataApiFactory $complaintItemDataApiFactory,
    ) {
        parent::__construct($tokenStorage);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function createComplaintMutation(Argument $argument, InputValidator $validator): Complaint
    {
        $this->runCheckUserIsLogged();

        $validator->validate();

        $input = $argument['input'];

        $order = $this->orderApiFacade->getByUuid($input['orderUuid']);
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($order->getCustomerUser() !== $customerUser) {
            throw new InvalidAccessUserError('You are not allowed to create complaint for this order');
        }

        $complaintItemsData = $this->createComplaintItems($input['items'], $order);


        $number = $this->complaintNumberSequenceRepository->getNextNumber();

        $complaintData = $this->complaintDataApiFactory->createFromComplaintInputArgument(
            $argument,
            $number,
            $order,
            $complaintItemsData,
            $customerUser,
        );

        return $this->complaintApiFacade->create($complaintData);
    }

    /**
     * @param array $complaintItemsInputData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[]
     */
    protected function createComplaintItems(array $complaintItemsInputData, Order $order): array
    {
        if (count($complaintItemsInputData) === 0) {
            throw new MissingComplaintItemsUserError('At least one complaint item must be provided');
        }

        $orderItemUuids = array_map(fn ($item) => $item['orderItemUuid'], $complaintItemsInputData);
        $orderItems = $this->orderItemApiFacade->findMappedByUuid($orderItemUuids);

        $complaintItemsData = [];

        foreach ($complaintItemsInputData as $item) {
            $orderItemUuid = $item['orderItemUuid'];

            if (!array_key_exists($orderItemUuid, $orderItems)) {
                throw new OrderItemNotFoundUserError(sprintf('Order item with UUID "%s" not found', $orderItemUuid));
            }

            $orderItem = $orderItems[$orderItemUuid];

            $this->validateComplaintItem($orderItem, $order, $item);

            $complaintItemsData[] = $this->complaintItemDataApiFactory->createFromComplaintItemInput($orderItem, $item);
        }

        return $complaintItemsData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param array $complaintItemInputData
     */
    protected function validateComplaintItem(OrderItem $orderItem, Order $order, array $complaintItemInputData): void
    {
        if ($orderItem->getOrder() !== $order) {
            throw new InvalidAccessUserError('You are not allowed to create complaint for this order item');
        }

        if ($complaintItemInputData['quantity'] > $orderItem->getQuantity()) {
            throw new InvalidQuantityUserError('Complaint item quantity is higher than order item quantity');
        }
    }
}
