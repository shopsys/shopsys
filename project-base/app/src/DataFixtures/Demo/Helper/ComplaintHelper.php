<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo\Helper;

use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ComplaintHelper
{
    private const UUID_NAMESPACE = '4bd62d36-8baa-4f8a-b074-c084641823b0';

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
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus $status
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData[] $items
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    public function createComplaint(
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
    public function createComplaintItemData(
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
    public function createUploadedFile(string $pathToImage): UploadedFile
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'complaint_demo_data_');
        copy($pathToImage, $tmpFile);

        return new UploadedFile($tmpFile, basename($pathToImage));
    }
}
