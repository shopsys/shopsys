<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Complaint;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;

class ComplaintItemDataApiFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemDataFactory $complaintItemDataFactory
     * @param \Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory $customerUploadedFileDataFactory
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ComplaintItemDataFactory $complaintItemDataFactory,
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
        protected readonly FileUpload $fileUpload,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem
     * @param array $item
     * @return \Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemData
     */
    public function createFromComplaintItemInput(OrderItem $orderItem, array $item): ComplaintItemData
    {
        $product = $orderItem->getProduct();
        $productName = $orderItem->getName();
        $catnum = $orderItem->getCatnum();

        if ($product) {
            $productName = $product->getName($this->domain->getLocale());
            $catnum = $product->getCatnum();
        }

        $complaintItemData = $this->complaintItemDataFactory->create();
        $complaintItemData->orderItem = $orderItem;
        $complaintItemData->product = $product;
        $complaintItemData->productName = $productName;
        $complaintItemData->catnum = $catnum;
        $complaintItemData->quantity = $item['quantity'];
        $complaintItemData->description = $item['description'];

        $complaintItemData->files = $this->customerUploadedFileDataFactory->create();

        foreach ($item['files'] as $uploadedFile) {
            $complaintItemData->files->uploadedFiles[] = $this->fileUpload->upload($uploadedFile);
            $complaintItemData->files->uploadedFilenames[] = $uploadedFile->getClientOriginalName();
        }

        return $complaintItemData;
    }
}
