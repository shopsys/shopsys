<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use App\Model\Customer\User\CustomerUser;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\InvalidQuantityUserError;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\MissingComplaintItemsUserError;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\OrderItemNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\InvalidAccessUserError;
use Symfony\Component\Security\Core\Security;

class ComplaintApiFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory $complaintFactory
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade $customerUploadedFileFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory $complaintItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository $complaintNumberSequenceRepository
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade $orderItemApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintDataApiFactory $complaintDataApiFactory
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintItemDataApiFactory $complaintItemDataApiFactory
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintRepository $complaintRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ComplaintFactory $complaintFactory,
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly ComplaintItemFactory $complaintItemFactory,
        protected readonly ComplaintNumberSequenceRepository $complaintNumberSequenceRepository,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ComplaintDataApiFactory $complaintDataApiFactory,
        protected readonly ComplaintItemDataApiFactory $complaintItemDataApiFactory,
        protected readonly Security $security,
        protected readonly ComplaintRepository $complaintRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintData $complaintData
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function create(ComplaintData $complaintData): Complaint
    {
        $complaintItemsData = [];
        $complaintItems = [];

        foreach ($complaintData->complaintItems as $key => $complaintItem) {
            $complaintItemsData[$key] = $complaintItem;
            $complaintItems[$key] = $this->complaintItemFactory->create($complaintItem);
        }

        $complaint = $this->complaintFactory->create($complaintData, $complaintItems);

        $this->em->persist($complaint);
        $this->em->flush();

        foreach ($complaintItems as $key => $item) {
            $this->customerUploadedFileFacade->manageFiles(
                $item,
                $complaintItemsData[$key]->files,
                UploadedFileTypeConfig::DEFAULT_TYPE_NAME,
                $complaint->getCustomerUser(),
            );
        }

        return $complaint;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function createFromComplaintInputArgument(Argument $argument): Complaint
    {
        $input = $argument['input'];

        $order = $this->orderApiFacade->getByUuid($input['orderUuid']);
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $orderCustomerUser = $order->getCustomerUser();

        if (!($orderCustomerUser === $customerUser || (
            $this->security->isGranted(CustomerUserRole::ROLE_API_ALL) &&
                $customerUser->getCustomer() === $order->getCustomer()
        ))
        ) {
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

        return $this->create($complaintData);
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

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCustomerUserComplaintsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
    ): array {
        return $this->complaintRepository->getCustomerUserComplaintsLimitedList($customerUser, $limit, $offset);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return int
     */
    public function getCustomerUserComplaintsLimitedListCount(CustomerUser $customerUser): int
    {
        return $this->complaintRepository->getCustomerUserComplaintsListCount($customerUser);
    }

    /**
     * @param string $complaintNumber
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function findByComplaintNumberAndCustomerUser(string $complaintNumber, CustomerUser $customerUser)
    {
        return $this->complaintRepository->findByComplaintNumberAndCustomerUser($complaintNumber, $customerUser);
    }
}
