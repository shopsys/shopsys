<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Complaint;

use App\Model\Order\Item\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Model\Complaint\Complaint;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintData;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintItemFactory;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintItemDataApiFactory;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintRepository;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\InvalidAccessUserError;
use Symfony\Component\Security\Core\Security;

class ComplaintApiFacadeTest extends TestCase
{
    private ComplaintApiFacade $complaintApiFacade;

    private MockObject $em;

    private MockObject $complaintFactory;

    private MockObject $customerUploadedFileFacade;

    private MockObject $complaintItemFactory;

    private MockObject $complaintNumberSequenceRepository;

    private MockObject $orderApiFacade;

    private MockObject $orderItemApiFacade;

    private MockObject $currentCustomerUser;

    private MockObject $complaintDataApiFactory;

    private MockObject $complaintItemDataApiFactory;

    private MockObject $security;

    private MockObject $complaintRepository;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->complaintFactory = $this->createMock(ComplaintFactory::class);
        $this->customerUploadedFileFacade = $this->createMock(CustomerUploadedFileFacade::class);
        $this->complaintItemFactory = $this->createMock(ComplaintItemFactory::class);
        $this->complaintNumberSequenceRepository = $this->createMock(ComplaintNumberSequenceRepository::class);
        $this->orderApiFacade = $this->createMock(OrderApiFacade::class);
        $this->orderItemApiFacade = $this->createMock(OrderItemApiFacade::class);
        $this->currentCustomerUser = $this->createMock(CurrentCustomerUser::class);
        $this->complaintDataApiFactory = $this->createMock(ComplaintDataApiFactory::class);
        $this->complaintItemDataApiFactory = $this->createMock(ComplaintItemDataApiFactory::class);
        $this->security = $this->createMock(Security::class);
        $this->complaintRepository = $this->createMock(ComplaintRepository::class);

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
            $this->security,
            $this->complaintRepository,
        );
    }

    public function testCreateFromComplaintInputArgumentForCustomerUserSuccess(): void
    {
        $argument = self::getCreateFromComplaintInputArgument();

        $order = $this->createMock(Order::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $complaintData = $this->createMock(ComplaintData::class);
        $complaint = $this->createMock(Complaint::class);

        $this->security->method('isGranted')->with(CustomerUserRole::ROLE_API_ALL)->willReturn(false);

        $this->orderApiFacade->method('getByUuid')->with('order-uuid')->willReturn($order);
        $this->currentCustomerUser->method('findCurrentCustomerUser')->willReturn($customerUser);
        $order->method('getCustomerUser')->willReturn($customerUser);

        $this->complaintNumberSequenceRepository->method('getNextNumber')->willReturn('123');
        $orderItemMock = $this->createMock(OrderItem::class);
        $orderItemMock->method('getOrder')->willReturn($order);
        $orderItemMock->method('getQuantity')->willReturn(1);

        $this->orderItemApiFacade->method('findMappedByUuid')->with(['item-uuid'])
            ->willReturn(['item-uuid' => $orderItemMock]);

        $this->complaintDataApiFactory->method('createFromComplaintInputArgument')
            ->willReturn($complaintData);
        $this->complaintFactory->method('create')->willReturn($complaint);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->complaintApiFacade->createFromComplaintInputArgument($argument);
        $this->assertSame($complaint, $result);
    }

    public function testCreateFromComplaintInputArgumentForCustomerUserInvalidAccessUserErrorMockObject(): void
    {
        $argument = self::getCreateFromComplaintInputArgument();

        $order = $this->createMock(Order::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customerUser2 = $this->createMock(CustomerUser::class);

        $this->security->method('isGranted')->with(CustomerUserRole::ROLE_API_ALL)->willReturn(false);

        $this->orderApiFacade->method('getByUuid')->with('order-uuid')->willReturn($order);
        $this->currentCustomerUser->method('findCurrentCustomerUser')->willReturn($customerUser);

        $order->method('getCustomerUser')->willReturn($customerUser2);

        $this->expectException(InvalidAccessUserError::class);
        $this->complaintApiFacade->createFromComplaintInputArgument($argument);
    }

    public function testCreateFromComplaintInputArgumentForCustomerSuccess(): void
    {
        $argument = self::getCreateFromComplaintInputArgument();

        $order = $this->createMock(Order::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $customer = $this->createMock(Customer::class);
        $orderCustomerUser = $this->createMock(CustomerUser::class);

        $complaintData = $this->createMock(ComplaintData::class);
        $complaint = $this->createMock(Complaint::class);

        $this->security->method('isGranted')->with(CustomerUserRole::ROLE_API_ALL)->willReturn(true);

        $customerUser->method('getCustomer')->willReturn($customer);

        $this->orderApiFacade->method('getByUuid')->with('order-uuid')->willReturn($order);
        $this->currentCustomerUser->method('findCurrentCustomerUser')->willReturn($customerUser);

        $order->method('getCustomerUser')->willReturn($orderCustomerUser);
        $order->method('getCustomer')->willReturn($customer);

        $this->complaintNumberSequenceRepository->method('getNextNumber')->willReturn('123');
        $orderItemMock = $this->createMock(OrderItem::class);
        $orderItemMock->method('getOrder')->willReturn($order);
        $orderItemMock->method('getQuantity')->willReturn(1);

        $this->orderItemApiFacade->method('findMappedByUuid')->with(['item-uuid'])
            ->willReturn(['item-uuid' => $orderItemMock]);

        $this->complaintDataApiFactory->method('createFromComplaintInputArgument')
            ->willReturn($complaintData);
        $this->complaintFactory->method('create')->willReturn($complaint);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->complaintApiFacade->createFromComplaintInputArgument($argument);
        $this->assertSame($complaint, $result);
    }

    public function testCreateFromComplaintInputArgumentForCustomerInvalidAccessUserError(): void
    {
        $argument = self::getCreateFromComplaintInputArgument();

        $order = $this->createMock(Order::class);
        $customerUser = $this->createMock(CustomerUser::class);
        $orderCustomerUser = $this->createMock(CustomerUser::class);
        $orderCustomer = $this->createMock(Customer::class);
        $orderCustomer2 = $this->createMock(Customer::class);

        $this->security->method('isGranted')->with(CustomerUserRole::ROLE_API_ALL)->willReturn(true);

        $customerUser->method('getCustomer')->willReturn($orderCustomer);

        $this->orderApiFacade->method('getByUuid')->with('order-uuid')->willReturn($order);
        $this->currentCustomerUser->method('findCurrentCustomerUser')->willReturn($customerUser);

        $order->method('getCustomerUser')->willReturn($orderCustomerUser);
        $order->method('getCustomer')->willReturn($orderCustomer2);

        $this->expectException(InvalidAccessUserError::class);
        $this->complaintApiFacade->createFromComplaintInputArgument($argument);
    }

    /**
     * @return \Overblog\GraphQLBundle\Definition\Argument
     */
    private static function getCreateFromComplaintInputArgument(): Argument
    {
        return new Argument([
            'input' => [
                'orderUuid' => 'order-uuid',
                'items' => [
                    ['orderItemUuid' => 'item-uuid', 'quantity' => 1],
                ],
            ],
        ]);
    }
}
