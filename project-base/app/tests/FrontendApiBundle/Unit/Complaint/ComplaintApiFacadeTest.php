<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Complaint;

use App\Model\Order\Item\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Complaint\Mail\ComplaintMailFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintItemDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintRepository;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;

class ComplaintApiFacadeTest extends TestCase
{
    private ComplaintApiFacade $complaintApiFacade;

    private MockObject $em;

    private Stub $complaintFactory;

    private Stub $customerUploadedFileFacade;

    private Stub $complaintItemFactory;

    private Stub $complaintNumberSequenceRepository;

    private MockObject $orderApiFacade;

    private MockObject $orderItemApiFacade;

    private Stub $currentCustomerUser;

    private Stub $complaintDataApiFactory;

    private Stub $complaintItemDataApiFactory;

    private Stub $complaintMailFacade;

    private Stub $complaintRepository;

    private Stub $withdrawalRequestFacade;

    #[Override]
    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->complaintFactory = $this->createStub(ComplaintFactory::class);
        $this->customerUploadedFileFacade = $this->createStub(CustomerUploadedFileFacade::class);
        $this->complaintItemFactory = $this->createStub(ComplaintItemFactory::class);
        $this->complaintNumberSequenceRepository = $this->createStub(ComplaintNumberSequenceRepository::class);
        $this->orderApiFacade = $this->createMock(OrderApiFacade::class);
        $this->orderItemApiFacade = $this->createMock(OrderItemApiFacade::class);
        $this->currentCustomerUser = $this->createStub(CurrentCustomerUser::class);
        $this->complaintDataApiFactory = $this->createStub(ComplaintDataApiFactory::class);
        $this->complaintItemDataApiFactory = $this->createStub(ComplaintItemDataApiFactory::class);
        $this->complaintMailFacade = $this->createStub(ComplaintMailFacade::class);
        $this->complaintRepository = $this->createStub(ComplaintRepository::class);
        $this->withdrawalRequestFacade = $this->createStub(WithdrawalRequestFacade::class);

        $this->complaintApiFacade = new ComplaintApiFacade(
            $this->em,
            $this->complaintFactory,
            $this->customerUploadedFileFacade,
            $this->complaintItemFactory,
            $this->complaintNumberSequenceRepository,
            $this->orderApiFacade,
            $this->orderItemApiFacade,
            $this->currentCustomerUser,
            $this->complaintDataApiFactory,
            $this->complaintItemDataApiFactory,
            $this->complaintMailFacade,
            $this->complaintRepository,
            $this->withdrawalRequestFacade,
        );
    }

    public function testCreateFromComplaintInputArgumentForCustomerUserSuccess(): void
    {
        $argument = self::getCreateFromComplaintInputArgument();

        $order = $this->createStub(Order::class);
        $customerUser = $this->createStub(CustomerUser::class);
        $complaintData = $this->createStub(ComplaintData::class);
        $complaint = $this->createStub(Complaint::class);

        $this->orderApiFacade->expects($this->any())->method('getByUuid')->with('order-uuid')->willReturn($order);
        $this->currentCustomerUser->method('findCurrentCustomerUser')->willReturn($customerUser);
        $order->method('getCustomerUser')->willReturn($customerUser);

        $this->complaintNumberSequenceRepository->method('getNextNumber')->willReturn('123');
        $orderItemStub = $this->createStub(OrderItem::class);
        $orderItemStub->method('getOrder')->willReturn($order);
        $orderItemStub->method('getQuantity')->willReturn(1);

        $this->orderItemApiFacade->expects($this->any())->method('findMappedByUuid')
            ->with(['item-uuid'])->willReturn(['item-uuid' => $orderItemStub]);

        $this->complaintDataApiFactory->method('createFromComplaintInputArgument')
            ->willReturn($complaintData);
        $this->complaintFactory->method('create')->willReturn($complaint);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->complaintApiFacade->createFromComplaintInputArgument($argument);
        $this->assertSame($complaint, $result);
    }

    public function testCreateFromComplaintInputArgumentForCustomerSuccess(): void
    {
        $argument = self::getCreateFromComplaintInputArgument();

        $order = $this->createStub(Order::class);
        $customerUser = $this->createStub(CustomerUser::class);
        $customer = $this->createStub(Customer::class);
        $orderCustomerUser = $this->createStub(CustomerUser::class);

        $complaintData = $this->createStub(ComplaintData::class);
        $complaint = $this->createStub(Complaint::class);

        $customerUser->method('getCustomer')->willReturn($customer);

        $this->orderApiFacade->expects($this->any())->method('getByUuid')->with('order-uuid')->willReturn($order);
        $this->currentCustomerUser->method('findCurrentCustomerUser')->willReturn($customerUser);

        $order->method('getCustomerUser')->willReturn($orderCustomerUser);
        $order->method('getCustomer')->willReturn($customer);

        $this->complaintNumberSequenceRepository->method('getNextNumber')->willReturn('123');
        $orderItemStub = $this->createStub(OrderItem::class);
        $orderItemStub->method('getOrder')->willReturn($order);
        $orderItemStub->method('getQuantity')->willReturn(1);

        $this->orderItemApiFacade->expects($this->any())->method('findMappedByUuid')
            ->with(['item-uuid'])->willReturn(['item-uuid' => $orderItemStub]);

        $this->complaintDataApiFactory->method('createFromComplaintInputArgument')
            ->willReturn($complaintData);
        $this->complaintFactory->method('create')->willReturn($complaint);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->complaintApiFacade->createFromComplaintInputArgument($argument);
        $this->assertSame($complaint, $result);
    }

    private static function getCreateFromComplaintInputArgument(): Argument
    {
        return new Argument([
            'input' => [
                'orderUuid' => 'order-uuid',
                'items' => [
                    ['orderItemUuid' => 'item-uuid', 'quantity' => 1],
                ],
                'resolution' => 'fix',
                'bankAccountNumber' => null,
            ],
        ]);
    }
}
