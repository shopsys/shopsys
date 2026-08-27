<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\UploadedFile\Config\UploadedFileTypeConfig;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMailFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatusFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\InvalidQuantityUserError;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\MissingComplaintItemsUserError;
use Shopsys\FrontendApiBundle\Model\Complaint\Exception\OrderItemNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\InvalidAccessUserError;

class ComplaintApiFacade
{
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
        protected readonly ComplaintMailFacade $complaintMailFacade,
        protected readonly ComplaintRepository $complaintRepository,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly ComplaintStatusFacade $complaintStatusFacade,
    ) {
    }

    public function create(ComplaintData $complaintData): Complaint
    {
        if ($complaintData->order !== null) {
            $this->checkOrderHasNoWithdrawalRequest($complaintData->order);
        }

        $complaintItemsData = [];
        $complaintItems = [];

        foreach ($complaintData->complaintItems as $key => $complaintItem) {
            $complaintItemsData[$key] = $complaintItem;
            $complaintItems[$key] = $this->complaintItemFactory->create($complaintItem);
        }

        $complaint = $this->complaintFactory->create($complaintData, $complaintItems);

        $this->em->persist($complaint);
        $this->em->flush();

        $this->complaintMailFacade->sendEmail($complaint);

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

    public function createFromComplaintInputArgument(Argument $argument): Complaint
    {
        $input = $argument['input'];

        $orderUuid = $input['orderUuid'];
        $order = null;

        if ($orderUuid !== null) {
            $order = $this->orderApiFacade->getByUuid($orderUuid);
            $this->checkOrderHasNoWithdrawalRequest($order);
        }
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $complaintItemsData = $this->createComplaintItems($input['items'], $order);

        $number = $this->complaintNumberSequenceRepository->getNextNumber();

        $complaintData = $this->complaintDataApiFactory->createFromComplaintInputArgument(
            $argument,
            $number,
            $order,
            $complaintItemsData,
            $input['resolution'],
            $input['bankAccountNumber'],
            $customerUser,
        );

        return $this->create($complaintData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[]
     */
    protected function createComplaintItems(array $complaintItemsInputData, ?Order $order): array
    {
        if (count($complaintItemsInputData) === 0) {
            throw new MissingComplaintItemsUserError('At least one complaint item must be provided');
        }

        if ($order === null) {
            return $this->createComplaintItemsWithoutOrder($complaintItemsInputData);
        }

        return $this->createComplaintItemsWithOrder($complaintItemsInputData, $order);
    }

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
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerUserComplaintsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        ComplaintFilter $filter,
    ): array {
        return $this->complaintRepository->getCustomerUserComplaintsLimitedList(
            $customerUser,
            $limit,
            $offset,
            $filter,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint[]
     */
    public function getCustomerComplaintsLimitedList(
        Customer $customer,
        int $limit,
        int $offset,
        ComplaintFilter $filter,
    ): array {
        return $this->complaintRepository->getCustomerComplaintsLimitedList($customer, $limit, $offset, $filter);
    }

    public function getCustomerUserComplaintsLimitedListCount(
        CustomerUser $customerUser,
        ComplaintFilter $filter,
    ): int {
        return $this->complaintRepository->getCustomerUserComplaintsListCount($customerUser, $filter);
    }

    public function getCustomerComplaintsLimitedListCount(
        Customer $customer,
        ComplaintFilter $filter,
    ): int {
        return $this->complaintRepository->getCustomerComplaintsListCount($customer, $filter);
    }

    /**
     * @return array<int, array{status: array{code: string, type: string, name: string}, count: int}>
     */
    public function getCustomerUserComplaintStatusCounts(
        CustomerUser $customerUser,
        ComplaintFilter $filter,
        string $locale,
    ): array {
        return $this->getComplaintStatusesWithCounts(
            $this->complaintRepository->getCustomerUserComplaintStatusCounts($customerUser, $filter),
            $locale,
        );
    }

    /**
     * @return array<int, array{status: array{code: string, type: string, name: string}, count: int}>
     */
    public function getCustomerComplaintStatusCounts(
        Customer $customer,
        ComplaintFilter $filter,
        string $locale,
    ): array {
        return $this->getComplaintStatusesWithCounts(
            $this->complaintRepository->getCustomerComplaintStatusCounts($customer, $filter),
            $locale,
        );
    }

    /**
     * @param array<int, int> $countsByStatusId
     * @return array<int, array{status: array{code: string, type: string, name: string}, count: int}>
     */
    protected function getComplaintStatusesWithCounts(array $countsByStatusId, string $locale): array
    {
        return array_map(
            fn (ComplaintStatus $complaintStatus): array => [
                'status' => $this->createComplaintStatusData($complaintStatus, $locale),
                'count' => $countsByStatusId[$complaintStatus->getId()] ?? 0,
            ],
            $this->complaintStatusFacade->getAll(),
        );
    }

    /**
     * @return array{code: string, type: string, name: string}
     */
    protected function createComplaintStatusData(ComplaintStatus $complaintStatus, string $locale): array
    {
        return [
            'code' => $complaintStatus->getCode(),
            'type' => $complaintStatus->getStatusType(),
            'name' => $complaintStatus->getName($locale),
        ];
    }

    public function findByComplaintNumberAndCustomerUser(
        string $complaintNumber,
        CustomerUser $customerUser,
    ): ?Complaint {
        return $this->complaintRepository->findByComplaintNumberAndCustomerUser($complaintNumber, $customerUser);
    }

    public function findByComplaintNumberAndCustomer(
        string $complaintNumber,
        Customer $customer,
    ): ?Complaint {
        return $this->complaintRepository->findByComplaintNumberAndCustomer($complaintNumber, $customer);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[]
     */
    protected function createComplaintItemsWithOrder(array $complaintItemsInputData, Order $order): array
    {
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

            $complaintItemsData[] = $this->complaintItemDataApiFactory->createFromComplaintItemInput($item, $orderItem);
        }

        return $complaintItemsData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[]
     */
    protected function createComplaintItemsWithoutOrder(array $complaintItemsInputData): array
    {
        $complaintItemsData = [];

        foreach ($complaintItemsInputData as $item) {
            $complaintItemsData[] = $this->complaintItemDataApiFactory->createFromComplaintItemInput($item);
        }

        return $complaintItemsData;
    }

    protected function checkOrderHasNoWithdrawalRequest(Order $order): void
    {
        if ($this->withdrawalRequestFacade->findConfirmedByOrder($order) !== null) {
            throw new InvalidAccessUserError('Cannot create complaint for order with withdrawal request');
        }
    }
}
