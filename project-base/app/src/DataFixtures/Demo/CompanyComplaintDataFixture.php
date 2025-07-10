<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\Helper\ComplaintHelper;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;

class CompanyComplaintDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string COMPANY_OWNER_COMPLAINT = 'company_owner_complaint';
    public const string COMPANY_USER_COMPLAINT = 'company_user_complaint';

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
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if (!$domainConfig->isB2b()) {
                continue;
            }

            $domainId = $domainConfig->getId();
            $this->importCompanyComplaints($domainId);
        }
    }

    /**
     * @param int $domainId
     */
    private function importCompanyComplaints(int $domainId): void
    {
        $this->createComplaint(
            $this->getReference(CompanyOrderDataFixture::COMPANY_ORDER_1, Order::class),
            $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL, $domainId, CustomerUser::class),
            [$this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/404.jpg')],
            self::COMPANY_OWNER_COMPLAINT,
            $domainId,
        );

        $this->createComplaint(
            $this->getReference(CompanyOrderDataFixture::COMPANY_ORDER_2, Order::class),
            $this->getReferenceForDomain(CompanyDataFixture::B2B_COMPANY_USER_EMAIL, $domainId, CustomerUser::class),
            [$this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/405.jpg')],
            self::COMPANY_USER_COMPLAINT,
            $domainId,
        );
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Customer\User\CustomerUser $customer
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile[] $uploadedFiles
     * @param string $referenceName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Complaint\Complaint
     */
    private function createComplaint(
        Order $order,
        CustomerUser $customer,
        array $uploadedFiles,
        string $referenceName,
        int $domainId,
    ): Complaint {
        $orderItems = $order->getProductItems();
        $complaintItem = $this->complaintHelper->createComplaintItemData(array_shift($orderItems), 'Broken!', 1, $uploadedFiles);

        $complaint = $this->complaintHelper->createComplaint(
            $customer,
            $order,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class),
            $this->complaintResolutionEnum::FIX,
            [$complaintItem],
        );

        $this->addReferenceForDomain($referenceName, $complaint, $domainId);

        return $complaint;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDependencies(): array
    {
        return [
            CompanyOrderDataFixture::class,
            ComplaintStatusDataFixture::class,
        ];
    }
}
