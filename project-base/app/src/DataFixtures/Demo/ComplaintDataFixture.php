<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\Helper\ComplaintHelper;
use App\Model\Customer\User\CustomerUser;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;

class ComplaintDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string COMPLAINT_PREFIX = 'complaint_';
    public const string COMPLAINT_BANK_ACCOUNT_NUMBER = '6846460001/5500';

    /**
     * @param \App\DataFixtures\Demo\Helper\ComplaintHelper $complaintHelper
     * @param \Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum $complaintResolutionEnum
     */
    public function __construct(
        private readonly ComplaintHelper $complaintHelper,
        private readonly ComplaintResolutionEnum $complaintResolutionEnum,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser1 */
        $customerUser1 = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);
        /** @var \App\Model\Order\Order $order1 */
        $order1 = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1);
        $uploadedFile1 = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/400.jpg');
        $uploadedFile2 = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/401.jpg');
        $uploadedFile3 = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/402.jpg');
        $uploadedFile4 = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/403.jpg');

        $orderItems1 = $order1->getProductItems();
        $complaintItem1 = $this->complaintHelper->createComplaintItemData(array_shift($orderItems1), 'Both broken!', 2, [$uploadedFile1, $uploadedFile2]);
        $complaintItem2 = $this->complaintHelper->createComplaintItemData(array_shift($orderItems1), 'Broken!', 1, [$uploadedFile3]);
        $complaint1 = $this->complaintHelper->createComplaint(
            $customerUser1,
            $order1,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class),
            $this->complaintResolutionEnum::FIX,
            [$complaintItem1, $complaintItem2],
        );
        $this->addReference(self::COMPLAINT_PREFIX . 1, $complaint1);

        /** @var \App\Model\Order\Order $order2 */
        $order2 = $this->getReference(OrderDataFixture::ORDER_PREFIX . 2);
        $orderItems2 = $order2->getProductItems();
        $complaintItem2 = $this->complaintHelper->createComplaintItemData(reset($orderItems2), 'Broken!', 1, [$uploadedFile4]);
        $complaint2 = $this->complaintHelper->createComplaint(
            $customerUser1,
            $order2,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_RESOLVED, ComplaintStatus::class),
            $this->complaintResolutionEnum::MONEY_RETURN,
            [$complaintItem2],
            self::COMPLAINT_BANK_ACCOUNT_NUMBER,
        );
        $this->addReference(self::COMPLAINT_PREFIX . 2, $complaint2);

        $this->createComplaintWithoutOrder();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            OrderDataFixture::class,
            ComplaintStatusDataFixture::class,
        ];
    }

    private function createComplaintWithoutOrder(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 5, CustomerUser::class);
        $uploadedFile = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/400.jpg');
        $complaintItemData = $this->complaintHelper->createComplaintItemData(
            null,
            'This is not what I expected :(',
            1,
            [$uploadedFile],
            'Hello Kitty',
            '9177759',
        );

        $this->complaintHelper->createComplaint(
            $customerUser,
            null,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class),
            $this->complaintResolutionEnum::FIX,
            [$complaintItemData],
            '42',
        );
    }
}
