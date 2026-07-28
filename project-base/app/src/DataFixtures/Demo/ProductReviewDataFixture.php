<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Product\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewData;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewDataFactory;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewEnabledChecker;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewFactory;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use Symfony\Component\Clock\DatePoint;

final class ProductReviewDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    private const string UUID_NAMESPACE = 'c48a90aa-d81d-4302-8092-98e7fe86d731';

    public const string PRODUCT_REVIEW_PENDING_CUSTOMER_FIRST_PRODUCT = 'product_review_pending_customer_first_product';
    public const string PRODUCT_REVIEW_APPROVED_VERIFIED_WITHOUT_ORDER_ITEM = 'product_review_approved_verified_without_order_item';
    public const string PRODUCT_REVIEW_APPROVED_WITHOUT_PRODUCT = 'product_review_approved_without_product';
    public const string PRODUCT_REVIEW_APPROVED_WITHOUT_TEXT = 'product_review_approved_without_text';
    public const string PRODUCT_REVIEW_APPROVED_ANONYMOUS_VARIANT = 'product_review_approved_anonymous_variant';
    public const string PRODUCT_REVIEW_APPROVED_GUEST = 'product_review_approved_guest';
    public const string PRODUCT_REVIEW_APPROVED_SECOND_OF_SAME_PRODUCT = 'product_review_approved_second_of_same_product';
    public const string PRODUCT_REVIEW_APPROVED_TV_SHARP_PICTURE = 'product_review_approved_tv_sharp_picture';
    public const string PRODUCT_REVIEW_APPROVED_TV_COMPACT_SIZE = 'product_review_approved_tv_compact_size';
    public const string PRODUCT_REVIEW_APPROVED_TV_REMOTE_CONTROL = 'product_review_approved_tv_remote_control';
    public const string PRODUCT_REVIEW_APPROVED_TV_ANONYMOUS = 'product_review_approved_tv_anonymous';
    public const string PRODUCT_REVIEW_APPROVED_TV_SOUND = 'product_review_approved_tv_sound';
    public const string PRODUCT_REVIEW_APPROVED_TV_CHILDREN_ROOM = 'product_review_approved_tv_children_room';
    public const string PRODUCT_REVIEW_APPROVED_TV_CHANNEL_TUNING = 'product_review_approved_tv_channel_tuning';
    public const string PRODUCT_REVIEW_APPROVED_TV_WITHOUT_TEXT = 'product_review_approved_tv_without_text';
    public const string PRODUCT_REVIEW_PENDING_CUSTOMER = 'product_review_pending_customer';
    public const string PRODUCT_REVIEW_PENDING_GUEST = 'product_review_pending_guest';
    public const string PRODUCT_REVIEW_PENDING_ANONYMOUS = 'product_review_pending_anonymous';
    public const string PRODUCT_REVIEW_REJECTED_CUSTOMER = 'product_review_rejected_customer';
    public const string PRODUCT_REVIEW_REJECTED_GUEST = 'product_review_rejected_guest';
    public const string PRODUCT_REVIEW_REJECTED_ANONYMOUS = 'product_review_rejected_anonymous';

    public function __construct(
        private readonly CustomerUserRepository $customerUserRepository,
        private readonly ProductReviewDataFactory $productReviewDataFactory,
        private readonly ProductReviewEnabledChecker $productReviewEnabledChecker,
        private readonly ProductReviewFactory $productReviewFactory,
    ) {
    }

    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if (!$this->productReviewEnabledChecker->isEnabledForDomain($domainConfig->getId())) {
                continue;
            }

            $this->createApprovedProductReviews($manager, $domainConfig);
            $this->createPendingProductReviews($manager, $domainConfig);
            $this->createRejectedProductReviews($manager, $domainConfig);
        }
    }

    private function createApprovedProductReviews(ObjectManager $manager, DomainConfig $domainConfig): void
    {
        $customerUser = $this->getCustomerUser($domainConfig->getId());

        $this->createApprovedProductReviewsWithCustomer($manager, $domainConfig, $customerUser);
        $this->createApprovedProductReviewsWithoutCustomer($manager, $domainConfig);
        $this->createAdditionalApprovedReviewsForFirstProduct($manager, $domainConfig);
    }

    private function createApprovedProductReviewsWithCustomer(
        ObjectManager $manager,
        DomainConfig $domainConfig,
        CustomerUser $customerUser,
    ): void {
        $locale = $domainConfig->getLocale();

        $productReviewData = $this->createProductReviewDataForProductReference('4', $domainConfig);
        $this->setCustomerUserData($productReviewData, $customerUser);
        $productReviewData->rating = 2;
        $productReviewData->ipAddress = '192.0.2.4';
        $productReviewData->createdAt = new DatePoint('2026-05-29 13:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
        $productReviewData->responseText = t('Thank you for your review. We are sorry the product did not fully meet your expectations.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->responseCreatedAt = new DatePoint('2026-05-30 09:00:00');
        $this->createProductReview($manager, self::PRODUCT_REVIEW_APPROVED_WITHOUT_TEXT, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('148', $domainConfig);
        $this->setCustomerUserData($productReviewData, $customerUser);
        $productReviewData->isAnonymous = true;
        $productReviewData->rating = 1;
        $productReviewData->text = t('The selected variant did not meet my needs.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '2001:db8::1';
        $productReviewData->createdAt = new DatePoint('2026-05-28 14:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
        $productReviewData->responseText = t('We are sorry the selected variant did not meet your needs. Our customer care team will be happy to help you choose a more suitable product.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->responseCreatedAt = new DatePoint('2026-05-29 09:00:00');
        $this->createProductReview($manager, self::PRODUCT_REVIEW_APPROVED_ANONYMOUS_VARIANT, $productReviewData);
    }

    private function createApprovedProductReviewsWithoutCustomer(
        ObjectManager $manager,
        DomainConfig $domainConfig,
    ): void {
        $locale = $domainConfig->getLocale();

        $productReviewData = $this->createProductReviewDataForProductReference('2', $domainConfig);
        $productReviewData->firstName = 'April';
        $productReviewData->lastName = 'Ryan';
        $productReviewData->email = 'april.ryan@example.com';
        $productReviewData->rating = 4;
        $productReviewData->text = t('The quality is excellent and the product works exactly as described.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.2';
        $productReviewData->isVerifiedPurchase = true;
        $productReviewData->createdAt = new DatePoint('2026-05-31 11:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_APPROVED_VERIFIED_WITHOUT_ORDER_ITEM, $productReviewData);

        $productReviewData = $this->createProductReviewDataWithoutProduct($domainConfig);
        $productReviewData->firstName = 'Cate';
        $productReviewData->lastName = 'Archer';
        $productReviewData->email = 'cate.archer@example.com';
        $productReviewData->rating = 3;
        $productReviewData->text = t('I would like to see this product available again.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.3';
        $productReviewData->createdAt = new DatePoint('2026-05-30 12:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_APPROVED_WITHOUT_PRODUCT, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('5', $domainConfig);
        $productReviewData->firstName = 'Razputin';
        $productReviewData->lastName = 'Aquato';
        $productReviewData->email = 'razputin.aquato@example.com';
        $productReviewData->rating = 5;
        $productReviewData->text = t('A great product for a very reasonable price.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.5';
        $productReviewData->createdAt = new DatePoint('2026-05-27 15:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_APPROVED_GUEST, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('1', $domainConfig);
        $productReviewData->firstName = 'Manny';
        $productReviewData->lastName = 'Calavera';
        $productReviewData->email = 'manny.calavera@example.com';
        $productReviewData->rating = 3;
        $productReviewData->text = t('It does the job, but I expected a bit more.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.6';
        $productReviewData->createdAt = new DatePoint('2026-05-26 09:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_APPROVED_SECOND_OF_SAME_PRODUCT, $productReviewData);
    }

    private function createPendingProductReviews(ObjectManager $manager, DomainConfig $domainConfig): void
    {
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();

        $customerUser = $this->getCustomerUser($domainId);

        $productReviewData = $this->createProductReviewDataForProductReference('1', $domainConfig);
        $this->setCustomerUserData($productReviewData, $customerUser);
        $productReviewData->rating = 5;
        $productReviewData->text = t('The product exceeded my expectations and was delivered quickly.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.1';
        $productReviewData->createdAt = new DatePoint('2026-06-01 10:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_PENDING;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_PENDING_CUSTOMER_FIRST_PRODUCT, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('6', $domainConfig);
        $this->setCustomerUserData($productReviewData, $customerUser);
        $productReviewData->rating = 4;
        $productReviewData->text = t('I need a few more days to form a final opinion.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.6';
        $productReviewData->createdAt = new DatePoint('2026-06-05 10:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_PENDING;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_PENDING_CUSTOMER, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('7', $domainConfig);
        $productReviewData->firstName = 'Nina';
        $productReviewData->lastName = 'Kalenkov';
        $productReviewData->email = 'nina.kalenkov@example.com';
        $productReviewData->rating = 3;
        $productReviewData->text = t('The product arrived in good condition.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.7';
        $productReviewData->createdAt = new DatePoint('2026-06-05 11:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_PENDING;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_PENDING_GUEST, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('148', $domainConfig);
        $productReviewData->firstName = 'Faith';
        $productReviewData->lastName = 'Connors';
        $productReviewData->email = 'faith.connors@example.com';
        $productReviewData->isAnonymous = true;
        $productReviewData->rating = 2;
        $productReviewData->text = t('I have mixed feelings about this variant.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '2001:db8::2';
        $productReviewData->createdAt = new DatePoint('2026-06-05 12:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_PENDING;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_PENDING_ANONYMOUS, $productReviewData);
    }

    private function createAdditionalApprovedReviewsForFirstProduct(
        ObjectManager $manager,
        DomainConfig $domainConfig,
    ): void {
        $locale = $domainConfig->getLocale();

        /**
         * @var array<int, array{
         *     referenceName: string,
         *     firstName: string,
         *     lastName: string,
         *     email: string,
         *     rating: int,
         *     text: string|null,
         *     ipAddress: string,
         *     isAnonymous: bool,
         *     isVerifiedPurchase: bool,
         *     createdAt: \DateTimeImmutable,
         *     responseText: string|null,
         *     responseCreatedAt: \DateTimeImmutable|null,
         * }> $reviewsData
         */
        $reviewsData = [
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_SHARP_PICTURE,
                'firstName' => 'Lena',
                'lastName' => 'Morris',
                'email' => 'lena.morris@example.com',
                'rating' => 4,
                'text' => t('The picture is sharp and the pink design looks great in the children\'s room.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '192.0.2.11',
                'isAnonymous' => false,
                'isVerifiedPurchase' => true,
                'createdAt' => new DatePoint('2026-05-31 16:00:00'),
                'responseText' => t('We are glad the television found the perfect place in your home.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'responseCreatedAt' => new DatePoint('2026-06-02 09:00:00'),
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_COMPACT_SIZE,
                'firstName' => 'Oliver',
                'lastName' => 'Reed',
                'email' => 'oliver.reed@example.com',
                'rating' => 5,
                'text' => t('Compact, easy to set up and ideal for a smaller room.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '192.0.2.12',
                'isAnonymous' => false,
                'isVerifiedPurchase' => false,
                'createdAt' => new DatePoint('2026-05-30 18:00:00'),
                'responseText' => null,
                'responseCreatedAt' => null,
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_REMOTE_CONTROL,
                'firstName' => 'Noah',
                'lastName' => 'Bennett',
                'email' => 'noah.bennett@example.com',
                'rating' => 3,
                'text' => t('The television works well, but the remote control buttons could be easier to read.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '192.0.2.13',
                'isAnonymous' => false,
                'isVerifiedPurchase' => true,
                'createdAt' => new DatePoint('2026-05-29 12:00:00'),
                'responseText' => t('Thank you for the feedback. We have passed your note about the remote control to the manufacturer.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'responseCreatedAt' => new DatePoint('2026-05-31 08:30:00'),
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_ANONYMOUS,
                'firstName' => 'Mia',
                'lastName' => 'Carter',
                'email' => 'mia.carter@example.com',
                'rating' => 4,
                'text' => t('The colors are vivid and the size is just right for a desk.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '2001:db8::4',
                'isAnonymous' => true,
                'isVerifiedPurchase' => false,
                'createdAt' => new DatePoint('2026-05-28 17:00:00'),
                'responseText' => null,
                'responseCreatedAt' => null,
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_SOUND,
                'firstName' => 'Emma',
                'lastName' => 'Wilson',
                'email' => 'emma.wilson@example.com',
                'rating' => 2,
                'text' => t('The design is lovely, but the built-in speakers sound weaker than I expected.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '192.0.2.14',
                'isAnonymous' => false,
                'isVerifiedPurchase' => true,
                'createdAt' => new DatePoint('2026-05-27 11:00:00'),
                'responseText' => t('We are sorry the sound did not meet your expectations. Our support team can help you choose compatible external speakers.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'responseCreatedAt' => new DatePoint('2026-05-29 10:00:00'),
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_CHILDREN_ROOM,
                'firstName' => 'Lucas',
                'lastName' => 'Martin',
                'email' => 'lucas.martin@example.com',
                'rating' => 5,
                'text' => t('My daughter loves it and the Full HD picture is surprisingly good for this size.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '192.0.2.15',
                'isAnonymous' => false,
                'isVerifiedPurchase' => true,
                'createdAt' => new DatePoint('2026-05-25 14:00:00'),
                'responseText' => t('That is wonderful to hear. Thank you for choosing our shop.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'responseCreatedAt' => new DatePoint('2026-05-26 09:30:00'),
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_CHANNEL_TUNING,
                'firstName' => 'Sofia',
                'lastName' => 'Brown',
                'email' => 'sofia.brown@example.com',
                'rating' => 1,
                'text' => t('Channel tuning was confusing and took much longer than expected.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'ipAddress' => '192.0.2.16',
                'isAnonymous' => false,
                'isVerifiedPurchase' => false,
                'createdAt' => new DatePoint('2026-05-24 10:00:00'),
                'responseText' => t('Please contact our support team. We will guide you through the channel setup step by step.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                'responseCreatedAt' => new DatePoint('2026-05-24 15:00:00'),
            ],
            [
                'referenceName' => self::PRODUCT_REVIEW_APPROVED_TV_WITHOUT_TEXT,
                'firstName' => 'Ethan',
                'lastName' => 'Clark',
                'email' => 'ethan.clark@example.com',
                'rating' => 4,
                'text' => null,
                'ipAddress' => '192.0.2.17',
                'isAnonymous' => false,
                'isVerifiedPurchase' => true,
                'createdAt' => new DatePoint('2026-05-23 09:00:00'),
                'responseText' => null,
                'responseCreatedAt' => null,
            ],
        ];

        foreach ($reviewsData as $reviewData) {
            $productReviewData = $this->createProductReviewDataForProductReference('1', $domainConfig);
            $productReviewData->firstName = $reviewData['firstName'];
            $productReviewData->lastName = $reviewData['lastName'];
            $productReviewData->email = $reviewData['email'];
            $productReviewData->rating = $reviewData['rating'];
            $productReviewData->text = $reviewData['text'];
            $productReviewData->ipAddress = $reviewData['ipAddress'];
            $productReviewData->isAnonymous = $reviewData['isAnonymous'];
            $productReviewData->isVerifiedPurchase = $reviewData['isVerifiedPurchase'];
            $productReviewData->createdAt = $reviewData['createdAt'];
            $productReviewData->responseText = $reviewData['responseText'];
            $productReviewData->responseCreatedAt = $reviewData['responseCreatedAt'];
            $productReviewData->status = ProductReviewStatusEnum::STATUS_APPROVED;
            $this->createProductReview($manager, $reviewData['referenceName'], $productReviewData);
        }
    }

    private function createRejectedProductReviews(ObjectManager $manager, DomainConfig $domainConfig): void
    {
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();

        $customerUser = $this->getCustomerUser($domainId);

        $productReviewData = $this->createProductReviewDataForProductReference('9', $domainConfig);
        $this->setCustomerUserData($productReviewData, $customerUser);
        $productReviewData->rating = 1;
        $productReviewData->text = t('This product is terrible, and anyone who buys it has no idea what they are doing.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.9';
        $productReviewData->rejectionReason = t('This review contains content that is not suitable for publication.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->createdAt = new DatePoint('2026-06-04 10:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_REJECTED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_REJECTED_CUSTOMER, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('10', $domainConfig);
        $productReviewData->firstName = 'Milo';
        $productReviewData->lastName = 'Thatch';
        $productReviewData->email = 'milo.thatch@example.com';
        $productReviewData->rating = 5;
        $productReviewData->text = t('This review contains an external advertisement.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '192.0.2.10';
        $productReviewData->rejectionReason = t('The review contains promotional content unrelated to the product.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->createdAt = new DatePoint('2026-06-04 11:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_REJECTED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_REJECTED_GUEST, $productReviewData);

        $productReviewData = $this->createProductReviewDataForProductReference('148', $domainConfig);
        $productReviewData->firstName = 'Zoe';
        $productReviewData->lastName = 'Castillo';
        $productReviewData->email = 'zoe.castillo@example.com';
        $productReviewData->isAnonymous = true;
        $productReviewData->rating = 5;
        $productReviewData->text = t('This review contains content that cannot be published.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->ipAddress = '2001:db8::3';
        $productReviewData->rejectionReason = t('The review does not meet our review guidelines.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productReviewData->createdAt = new DatePoint('2026-06-04 12:00:00');
        $productReviewData->status = ProductReviewStatusEnum::STATUS_REJECTED;
        $this->createProductReview($manager, self::PRODUCT_REVIEW_REJECTED_ANONYMOUS, $productReviewData);
    }

    private function createProductReviewDataForProductReference(
        string $productReferenceId,
        DomainConfig $domainConfig,
    ): ProductReviewData {
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceId, Product::class);
        $productReviewData = $this->productReviewDataFactory->create();
        $productReviewData->domainId = $domainId;
        $productReviewData->product = $product;
        $productReviewData->catnum = $product->getCatnum();
        $productReviewData->productName = $product->getName($locale);

        return $productReviewData;
    }

    private function createProductReviewDataWithoutProduct(DomainConfig $domainConfig): ProductReviewData
    {
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();

        $productReviewData = $this->productReviewDataFactory->create();
        $productReviewData->domainId = $domainId;
        $productReviewData->catnum = 'deleted-product-001';
        $productReviewData->productName = t('Discontinued product', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

        return $productReviewData;
    }

    private function createProductReview(
        ObjectManager $manager,
        string $referenceName,
        ProductReviewData $productReviewData,
    ): void {
        $domainId = $productReviewData->domainId;
        $productReviewData->uuid = Uuid::uuid5(self::UUID_NAMESPACE, $referenceName . $domainId)->toString();
        $productReview = $this->productReviewFactory->create($productReviewData);
        $productReview->edit($productReviewData);

        $manager->persist($productReview);
        $manager->flush();
        $this->addReferenceForDomain($referenceName, $productReview, $domainId);
    }

    private function getCustomerUser(int $domainId): CustomerUser
    {
        return $this->customerUserRepository->getCustomerUserByEmailAndDomain('no-reply@shopsys.com', $domainId);
    }

    private function setCustomerUserData(ProductReviewData $productReviewData, CustomerUser $customerUser): void
    {
        $productReviewData->customerUser = $customerUser;
        $productReviewData->firstName = $customerUser->getFirstName();
        $productReviewData->lastName = $customerUser->getLastName();
        $productReviewData->email = $customerUser->getEmail();
    }

    #[Override]
    public function getDependencies(): array
    {
        return [
            CustomerUserDataFixture::class,
            ProductDataFixture::class,
        ];
    }
}
