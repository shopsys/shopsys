<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\DataFixtures\Demo\Helper\ComplaintHelper;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Complaint\Status\ComplaintStatus;

class CompanyComplaintDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    public const string COMPLAINT_PREFIX = 'complaint_';

    /**
     * @param \App\DataFixtures\Demo\Helper\ComplaintHelper $complaintHelper
     */
    public function __construct(
        private readonly ComplaintHelper $complaintHelper,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        $customerUserNovotny = $this->getReferenceForDomain(CompanyDataFixture::COMPANY_USER_JOZEF_NOVOTNY, Domain::SECOND_DOMAIN_ID, CustomerUser::class);
        $customerUserKovac = $this->getReferenceForDomain(CompanyDataFixture::COMPANY_USER_PETER_KOVAC, Domain::SECOND_DOMAIN_ID, CustomerUser::class);

        $uploadedFile1 = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/404.jpg');
        $uploadedFile2 = $this->complaintHelper->createUploadedFile(__DIR__ . '/../resources/images/complaint/405.jpg');

        $order27 = $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 27, Order::class);
        $orderItems1 = $order27->getProductItems();
        $complaintItem1 = $this->complaintHelper->createComplaintItemData(array_shift($orderItems1), 'Broken!', 1, [$uploadedFile2]);
        $complaint1 = $this->complaintHelper->createComplaint(
            $customerUserNovotny,
            $order27,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class),
            [$complaintItem1],
        );
        $this->addReference(self::COMPLAINT_PREFIX . 3, $complaint1);

        $order28 = $this->getReference(CompanyOrderDataFixture::ORDER_PREFIX . 28, Order::class);
        $orderItems1 = $order28->getProductItems();
        $complaintItem1 = $this->complaintHelper->createComplaintItemData(array_shift($orderItems1), 'Broken!', 1, [$uploadedFile1]);
        $complaint2 = $this->complaintHelper->createComplaint(
            $customerUserKovac,
            $order28,
            $this->getReference(ComplaintStatusDataFixture::COMPLAINT_STATUS_NEW, ComplaintStatus::class),
            [$complaintItem1],
        );
        $this->addReference(self::COMPLAINT_PREFIX . 4, $complaint2);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CompanyOrderDataFixture::class,
            ComplaintStatusDataFixture::class,
        ];
    }
}
