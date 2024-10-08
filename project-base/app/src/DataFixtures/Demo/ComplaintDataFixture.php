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
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ComplaintDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = '4bd62d36-8baa-4f8a-b074-c084641823b0';
    public const string COMPLAINT_PREFIX = 'complaint_';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory $complaintDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory $complaintItemDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository $complaintNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory $customerUploadedFileDataFactory
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     */
    public function __construct(
        private readonly ComplaintDataFactory $complaintDataFactory,
        private readonly ComplaintItemDataFactory $complaintItemDataFactory,
        private readonly ComplaintApiFacade $complaintApiFacade,
        private readonly ComplaintNumberSequenceRepository $complaintNumberSequenceRepository,
        private readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
        private readonly FileUpload $fileUpload,
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
        $uploadedFile1 = $this->createUploadedFiles(__DIR__ . '/../resources/images/complaint/400.jpg');
        $uploadedFile2 = $this->createUploadedFiles(__DIR__ . '/../resources/images/complaint/401.jpg');
        $uploadedFile3 = $this->createUploadedFiles(__DIR__ . '/../resources/images/complaint/402.jpg');
        $uploadedFile4 = $this->createUploadedFiles(__DIR__ . '/../resources/images/complaint/403.jpg');

        $orderItems1 = $order1->getProductItems();
        $orderItem1 = $this->createComplaintItemData(array_shift($orderItems1), 'Both broken!', 2, [$uploadedFile1, $uploadedFile2]);
        $orderItem2 = $this->createComplaintItemData(array_shift($orderItems1), 'Broken!', 1, [$uploadedFile3]);
        $complaint1 = $this->createComplaint(
            $customerUser1,
            $order1,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class),
            [$orderItem1, $orderItem2],
        );
        $this->addReference(self::COMPLAINT_PREFIX . 1, $complaint1);

        /** @var \App\Model\Order\Order $order2 */
        $order2 = $this->getReference(OrderDataFixture::ORDER_PREFIX . 2);
        $orderItems2 = $order2->getProductItems();
        $orderItem2 = $this->createComplaintItemData(reset($orderItems2), 'Broken!', 1, [$uploadedFile4]);
        $complaint2 = $this->createComplaint(
            $customerUser1,
            $order2,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED, ComplaintStatus::class),
            [$orderItem2],
        );
        $this->addReference(self::COMPLAINT_PREFIX . 2, $complaint2);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            OrderDataFixture::class,
            ComplaintStatusDataFixture::class,
        ];
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $status
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[] $items
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    private function createComplaint(
        CustomerUser $customerUser,
        Order $order,
        ComplaintStatus $status,
        array $items,
    ): Complaint {
        $complaintData = $this->complaintDataFactory->create();
        $complaintData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, md5(serialize(func_get_args())))->toString();
        $complaintData->number = $this->complaintNumberSequenceRepository->getNextNumber();
        $complaintData->domainId = $order->getDomainId();
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
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile[] $uploadedFiles
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    private function createComplaintItemData(
        OrderItem $orderItem,
        string $description,
        int $quantity,
        array $uploadedFiles,
    ): ComplaintItemData {
        $item = $this->complaintItemDataFactory->create();

        $item->orderItem = $orderItem;
        $item->product = $orderItem->getProduct();
        $item->productName = $orderItem->getName();
        $item->catnum = $orderItem->getCatnum();
        $item->description = $description;
        $item->quantity = $quantity;
        $item->files = $this->customerUploadedFileDataFactory->create();

        foreach ($uploadedFiles as $uploadedFile) {
            $item->files->uploadedFiles[] = $this->fileUpload->upload($uploadedFile);
            $item->files->uploadedFilenames[] = $uploadedFile->getClientOriginalName();
        }

        return $item;
    }

    /**
     * @param string $pathToImage
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected function createUploadedFiles(string $pathToImage): UploadedFile
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'complaint_demo_data_');
        copy($pathToImage, $tmpFile);

        return new UploadedFile($tmpFile, basename($pathToImage));
    }
}
