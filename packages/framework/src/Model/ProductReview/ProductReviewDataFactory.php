<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

class ProductReviewDataFactory
{
    public function create(): ProductReviewData
    {
        return $this->createInstance();
    }

    public function createFromProductReview(ProductReview $productReview): ProductReviewData
    {
        $productReviewData = $this->createInstance();

        $productReviewData->uuid = $productReview->getUuid();
        $productReviewData->domainId = $productReview->getDomainId();
        $productReviewData->product = $productReview->getProduct();
        $productReviewData->catnum = $productReview->getCatnum();
        $productReviewData->productName = $productReview->getProductName();
        $productReviewData->orderItem = $productReview->getOrderItem();
        $productReviewData->customerUser = $productReview->getCustomerUser();
        $productReviewData->firstName = $productReview->getFirstName();
        $productReviewData->lastName = $productReview->getLastName();
        $productReviewData->email = $productReview->getEmail();
        $productReviewData->isAnonymous = $productReview->isAnonymous();
        $productReviewData->rating = $productReview->getRating();
        $productReviewData->text = $productReview->getText();
        $productReviewData->ipAddress = $productReview->getIpAddress();
        $productReviewData->isVerifiedPurchase = $productReview->isVerifiedPurchase();
        $productReviewData->status = $productReview->getStatus();
        $productReviewData->rejectionReason = $productReview->getRejectionReason();
        $productReviewData->responseText = $productReview->getResponseText();
        $productReviewData->responseCreatedAt = $productReview->getResponseCreatedAt();
        $productReviewData->createdAt = $productReview->getCreatedAt();

        return $productReviewData;
    }

    protected function createInstance(): ProductReviewData
    {
        return new ProductReviewData();
    }
}
