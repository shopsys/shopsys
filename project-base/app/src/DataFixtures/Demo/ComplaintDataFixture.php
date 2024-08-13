<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintStatusEnum;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;

class ComplaintDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const UUID_NAMESPACE = '4bd62d36-8baa-4f8a-b074-c084641823b0';
    public const string COMPLAINT_PREFIX = 'complaint_';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory $complaintDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory $complaintItemDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository $complaintNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory $customerUploadedFileDataFactory
     */
    public function __construct(
        private readonly ComplaintDataFactory $complaintDataFactory,
        private readonly ComplaintItemDataFactory $complaintItemDataFactory,
        private readonly ComplaintApiFacade $complaintApiFacade,
        private readonly ComplaintNumberSequenceRepository $complaintNumberSequenceRepository,
        private readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser1 */
        $customerUser1 = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);
        /** @var \App\Model\Order\Order $order1 */
        $order1 = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1);
        $orderItems1 = $order1->getProductItems();
        $orderItem1 = $this->createComplaintItemData(reset($orderItems1), 'Both broken!', 2);
        $complaint1 = $this->createComplaint($customerUser1, $order1, ComplaintStatusEnum::STATUS_NEW, [$orderItem1]);
        $this->addReference(self::COMPLAINT_PREFIX . 1, $complaint1);

        /** @var \App\Model\Order\Order $order2 */
        $order2 = $this->getReference(OrderDataFixture::ORDER_PREFIX . 2);
        $orderItems2 = $order2->getProductItems();
        $orderItem2 = $this->createComplaintItemData(reset($orderItems2), 'Broken!', 1);
        $complaint2 = $this->createComplaint($customerUser1, $order2, ComplaintStatusEnum::STATUS_RESOLVED, [$orderItem2]);
        $this->addReference(self::COMPLAINT_PREFIX . 2, $complaint2);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            OrderDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Order\Order $order
     * @param string $status
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[] $items
     *@throws \Shopsys\FrameworkBundle\Model\NumberSequence\Exception\NumberSequenceNotFoundException
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    private function createComplaint(
        CustomerUser $customerUser,
        Order $order,
        string $status,
        array $items,
    ): Complaint {
        $complaintData = $this->complaintDataFactory->create();
        $complaintData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, md5(serialize(func_get_args())))->toString();
        $complaintData->number = $this->complaintNumberSequenceRepository->getNextNumber();
        $complaintData->customerUser = $customerUser;
        $complaintData->order = $order;
        $complaintData->status = $status;
        $complaintData->complaintItems = $items;

        $deliveryAddress = $customerUser->getDefaultDeliveryAddress();
        $complaintData->deliveryFirstName = $deliveryAddress->getFirstName();
        $complaintData->deliveryLastName = $deliveryAddress->getLastName();
        $complaintData->deliveryStreet = $deliveryAddress->getStreet();
        $complaintData->deliveryCity = $deliveryAddress->getCity();
        $complaintData->deliveryPostcode = $deliveryAddress->getPostcode();
        $complaintData->deliveryCountry = $deliveryAddress->getCountry();
        $complaintData->deliveryTelephone = $deliveryAddress->getTelephone();
        $complaintData->deliveryCompanyName = $deliveryAddress->getCompanyName();

        return $this->complaintApiFacade->create($complaintData);
    }

    /**
     * @param \App\Model\Order\Item\OrderItem $orderItem
     * @param string $description
     * @param int $quantity
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    private function createComplaintItemData(
        OrderItem $orderItem,
        string $description,
        int $quantity,
    ): ComplaintItemData {
        $item = $this->complaintItemDataFactory->create();

        $item->orderItem = $orderItem;
        $item->description = $description;
        $item->quantity = $quantity;
        $item->files = $this->customerUploadedFileDataFactory->create();

        return $item;
    }
}
