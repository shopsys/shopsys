<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\ProductReview\Elasticsearch\ProductReviewDocumentMapper;
use Shopsys\FrameworkBundle\Model\ProductReview\Image\ProductReviewImage;
use Shopsys\FrameworkBundle\Model\ProductReview\Image\ProductReviewImageDataFactory;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewDataFactory;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\ProductReview\Exception\DuplicateProductReviewUserError;
use Shopsys\FrontendApiBundle\Model\ProductReview\Exception\ProductReviewsDisabledUserError;
use Shopsys\FrontendApiBundle\Model\ProductReview\Exception\ProductReviewVariantRequiredUserError;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductReviewApiFacade
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Domain $domain,
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        protected readonly ProductFacade $productFacade,
        protected readonly ProductReviewDataFactory $productReviewDataFactory,
        protected readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
        protected readonly ProductReviewDocumentMapper $productReviewDocumentMapper,
        protected readonly ProductReviewFacade $productReviewFacade,
        protected readonly RequestStack $requestStack,
        protected readonly ProductReviewImageDataFactory $productReviewImageDataFactory,
        protected readonly CustomerUploadedFileDataFactory $customerUploadedFileDataFactory,
        protected readonly FileUpload $fileUpload,
    ) {
    }

    public function checkProductReviewsEnabledOnCurrentDomain(): void
    {
        if (!$this->areProductReviewsEnabledOnCurrentDomain()) {
            throw new ProductReviewsDisabledUserError('Product reviews are not enabled on this domain.');
        }
    }

    public function areProductReviewsEnabledOnCurrentDomain(): bool
    {
        return $this->productReviewEnabledChecker->isEnabledForDomain($this->domain->getId());
    }

    /**
     * The reviews of the whole variant family live on the document of the main variant
     */
    public function getMainProductByUuid(string $productUuid): Product
    {
        $product = $this->productFacade->getVisibleByUuid(
            $productUuid,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        return $product->isVariant() ? $product->getMainVariant() : $product;
    }

    /**
     * The reviews of the whole family are aggregated on the main variant, so variants have no summary of their own
     * and clients holding a variant traverse to it instead, which keeps the field free of extra searches
     *
     * @param array<string, mixed> $productArray
     * @return array{average_rating: float|null, total_count: int, rating_counts: array<int, array{rating: int, count: int}>}|null
     */
    public function getReviewSummaryForProductArray(array $productArray): ?array
    {
        if ($productArray[ProductExportFieldProvider::IS_VARIANT] === true) {
            return null;
        }

        return $productArray[ProductExportFieldProvider::REVIEW_SUMMARY];
    }

    /**
     * @return array{average_rating: float|null, total_count: int, rating_counts: array<int, array{rating: int, count: int}>}|null
     */
    public function getReviewSummaryForProduct(Product $product): ?array
    {
        if ($product->isVariant()) {
            return null;
        }

        try {
            $productArray = $this->productElasticsearchProvider->getVisibleProductArrayById($product->getId());
        } catch (ProductNotFoundException) {
            return $this->productReviewDocumentMapper->mapSummary([]);
        }

        return $productArray[ProductExportFieldProvider::REVIEW_SUMMARY];
    }

    /**
     * Own reviews are read from the database, so the customer sees them regardless of moderation and export state
     *
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $mainProduct Null returns the reviews regardless of the reviewed product
     * @return array<int, array<string, mixed>>
     */
    public function getCustomerUserReviewArrays(
        CustomerUser $customerUser,
        ?Product $mainProduct,
        int $limit,
        int $offset,
    ): array {
        $productReviews = $this->productReviewFacade->getByCustomerUser(
            $customerUser,
            $this->domain->getId(),
            $this->getFamilyProductIds($mainProduct),
            $limit,
            $offset,
        );

        return array_map($this->extractReviewToPublicArray(...), $productReviews);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $mainProduct Null counts the reviews regardless of the reviewed product
     */
    public function getCustomerUserReviewsCount(CustomerUser $customerUser, ?Product $mainProduct): int
    {
        return $this->productReviewFacade->getCountByCustomerUser(
            $customerUser,
            $this->domain->getId(),
            $this->getFamilyProductIds($mainProduct),
        );
    }

    /**
     * @return int[]|null
     */
    protected function getFamilyProductIds(?Product $mainProduct): ?array
    {
        if ($mainProduct === null) {
            return null;
        }

        return [
            $mainProduct->getId(),
            ...array_map(static fn (Product $variant) => $variant->getId(), $mainProduct->getVariants()),
        ];
    }

    /**
     * Maps the entity to the very same shape the Elasticsearch export uses, so both sources share one GraphQL resolver map
     *
     * @return array<string, mixed>
     */
    public function extractReviewToPublicArray(ProductReview $productReview): array
    {
        $reviewArray = $this->productReviewDocumentMapper->mapReview($productReview, $this->domain->getId());
        $reviewArray['status'] = $productReview->getStatus();
        $reviewArray['rejection_reason'] = $productReview->getRejectionReason();
        $reviewArray['rejected_images_count'] = count(array_filter(
            $productReview->getImages(),
            static fn (ProductReviewImage $productReviewImage): bool => $productReviewImage->isRejected(),
        ));
        $reviewArray['product_id'] = $productReview->getProduct()?->getId();

        return $reviewArray;
    }

    public function createFromProductReviewInputArgument(
        Argument $argument,
        ?CustomerUser $customerUser,
    ): ProductReview {
        $input = $argument['input'];

        $product = $this->productFacade->getVisibleByUuid(
            $input['productUuid'],
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        if ($product->isMainVariant()) {
            throw new ProductReviewVariantRequiredUserError('A concrete variant of the product has to be reviewed.');
        }

        if ($customerUser !== null
            && $this->productReviewFacade->existsByCustomerUserAndProductId($customerUser, $product->getId(), $this->domain->getId())
        ) {
            throw new DuplicateProductReviewUserError('The customer has already reviewed this product.');
        }

        $orderItem = $this->resolveOrderItem($input, $customerUser, $product);

        $productReviewData = $this->productReviewDataFactory->create();
        $productReviewData->domainId = $this->domain->getId();
        $productReviewData->product = $product;
        $productReviewData->catnum = $product->getCatnum();
        $productReviewData->productName = (string)$product->getName($this->domain->getLocale());
        $productReviewData->orderItem = $orderItem;
        $productReviewData->customerUser = $customerUser;
        $productReviewData->firstName = $input['firstName'];
        $productReviewData->lastName = $input['lastName'];
        $productReviewData->email = $customerUser?->getEmail() ?? $input['email'];
        $productReviewData->isAnonymous = $input['isAnonymous'];
        $productReviewData->rating = $input['rating'];
        $productReviewData->text = $input['text'];
        $productReviewData->ipAddress = $this->requestStack->getCurrentRequest()?->getClientIp() ?? 'unknown';
        $productReviewData->isVerifiedPurchase = $orderItem !== null;
        $productReviewData->images = $this->createImagesData($input['images']);

        try {
            return $this->productReviewFacade->create($productReviewData);
        } catch (UniqueConstraintViolationException) {
            throw new DuplicateProductReviewUserError('The customer has already reviewed this product.');
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile[] $uploadedFiles
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\Image\ProductReviewImageData[]
     */
    protected function createImagesData(array $uploadedFiles): array
    {
        $imagesData = [];

        foreach (array_values($uploadedFiles) as $index => $uploadedFile) {
            $productReviewImageData = $this->productReviewImageDataFactory->create();
            $productReviewImageData->file = $this->customerUploadedFileDataFactory->create();
            $productReviewImageData->file->uploadedFiles[] = $this->fileUpload->upload($uploadedFile);
            $productReviewImageData->file->uploadedFilenames[] = sprintf('review-image-%d', $index + 1);

            $imagesData[] = $productReviewImageData;
        }

        return $imagesData;
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function resolveOrderItem(
        array $input,
        ?CustomerUser $customerUser,
        Product $product,
    ): ?OrderItem {
        if ($input['orderUrlHash'] !== null) {
            $orderItem = $this->orderItemApiFacade->findNewestReviewableOrderItemByOrderUrlHash(
                $input['orderUrlHash'],
                $product,
                $this->domain->getId(),
            );

            if ($orderItem !== null) {
                if ($this->productReviewFacade->existsByOrderAndProductId($orderItem->getOrder(), $product->getId())) {
                    throw new DuplicateProductReviewUserError('The product has already been reviewed from this order.');
                }

                return $orderItem;
            }
        }

        if ($customerUser === null) {
            return null;
        }

        return $this->orderItemApiFacade->findNewestReviewableOrderItemByCustomer(
            $customerUser->getCustomer(),
            $product,
            $this->domain->getId(),
        );
    }
}
