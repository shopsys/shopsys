<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Complaint;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Component\Constraints\ComplaintResolution;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CreateComplaintTest extends GraphQlWithLoginTestCase
{
    protected const string COMPLAINT_EMAIL = 'no-reply@shopsys.com';

    /**
     * @inject
     */
    protected ComplaintResolutionEnum $complaintResolutionEnum;

    /**
     * @inject
     */
    private WithdrawalRequestFacade $withdrawalRequestFacade;

    /**
     * @inject
     */
    private WithdrawalRequestDataFactory $withdrawalRequestDataFactory;

    public function testCreateComplaint(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1, Order::class);
        $orderItemProduct1 = $order->getProductItems()[0];
        $product1 = $orderItemProduct1->getProduct();
        $complaintItemQuantity1 = 1;
        $complaintItemFilesCount1 = 2;

        $orderItemProduct2 = $order->getProductItems()[1];
        $product2 = $orderItemProduct2->getProduct();
        $complaintItemQuantity2 = 2;
        $complaintItemFilesCount2 = 1;

        // Initialize product proxies before the GraphQL mutation to avoid
        // EntityIdentityCollisionException in ORM 3 (the mutation loads the same
        // entities into the identity map, causing collision on later proxy init)
        $product1Name = $product1->getName();
        $product1Catnum = $product1->getCatnum();
        $product2Name = $product2->getName();
        $product2Catnum = $product2->getCatnum();

        $response = $this->createComplaint(
            $order,
            $complaintItemQuantity1,
            $orderItemProduct1,
            $complaintItemQuantity2,
            $orderItemProduct2,
            $this->complaintResolutionEnum::FIX,
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertArrayHasKey('number', $responseData);

        $this->assertArrayHasKey('email', $responseData);
        $this->assertSame(self::COMPLAINT_EMAIL, $responseData['email']);

        $this->assertArrayHasKey('resolution', $responseData);
        $this->assertSame($this->complaintResolutionEnum::FIX, $responseData['resolution']['value']);

        $this->assertArrayHasKey('bankAccountNumber', $responseData);
        $this->assertNull($responseData['bankAccountNumber']);

        $this->assertArrayHasKey('items', $responseData);
        $this->assertCount(2, $responseData['items']);

        $this->assertComplaintItem(
            $responseData['items'][0],
            $complaintItemQuantity1,
            $complaintItemFilesCount1,
            $product1Name,
            $product1Catnum,
        );

        $this->assertComplaintItem(
            $responseData['items'][1],
            $complaintItemQuantity2,
            $complaintItemFilesCount2,
            $product2Name,
            $product2Catnum,
        );
    }

    public function testCreateMoneyReturnComplaintWithoutBankAccountNumber(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1, Order::class);

        $response = $this->createComplaint(
            $order,
            1,
            $order->getProductItems()[0],
            2,
            $order->getProductItems()[1],
            $this->complaintResolutionEnum::MONEY_RETURN,
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.bankAccountNumber', $violations);
        $this->assertSame(ComplaintResolution::SELECTED_COMPLAINT_RESOLUTION_REQUIRES_BANK_ACCOUNT_NUMBER_FILLED_ERROR, $violations['input.bankAccountNumber']['0']['code']);
    }

    public function testCreateMoneyReturnComplaintWithBankAccountNumber(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1, Order::class);

        $response = $this->createComplaint(
            $order,
            1,
            $order->getProductItems()[0],
            2,
            $order->getProductItems()[1],
            $this->complaintResolutionEnum::MONEY_RETURN,
            '6846460001/5500',
        );

        $this->assertArrayNotHasKey('errors', $response);
    }

    public function testCreateComplaintWithWrongResolution(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1, Order::class);

        $response = $this->createComplaint(
            $order,
            1,
            $order->getProductItems()[0],
            2,
            $order->getProductItems()[1],
            'notExistingResolution',
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.resolution', $violations);
        $this->assertSame(ComplaintResolution::SELECTED_COMPLAINT_RESOLUTION_NOT_SUPPORTED_ERROR, $violations['input.resolution']['0']['code']);
    }

    /**
     * @return array<string, mixed>
     */
    private function createComplaint(
        Order $order,
        int $complaintItemQuantity1,
        OrderItem $orderItemProduct1,
        int $complaintItemQuantity2,
        OrderItem $orderItemProduct2,
        string $resolution,
        ?string $bankAccountNumber = null,
    ): array {
        return $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'orderUuid' => $order->getUuid(),
                    'email' => self::COMPLAINT_EMAIL,
                    'items' => [
                        [
                            'quantity' => $complaintItemQuantity1,
                            'description' => 'Broken!!!',
                            'orderItemUuid' => $orderItemProduct1->getUuid(),
                            'files' => [null, null],
                        ],
                        [
                            'quantity' => $complaintItemQuantity2,
                            'description' => 'Broken 2!!!',
                            'orderItemUuid' => $orderItemProduct2->getUuid(),
                            'files' => [null],
                        ],
                    ],
                    'deliveryAddress' => [
                        'firstName' => 'Jiří',
                        'lastName' => 'Ševčík',
                        'street' => 'První 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => new PhoneData('CZ', '+420', '123456789'),
                        'country' => 'CZ',
                    ],
                    'resolution' => $resolution,
                    'bankAccountNumber' => $bankAccountNumber,
                ],
            ],
            [
                1 => __DIR__ . '/files/1.jpg',
                2 => __DIR__ . '/files/2.jpg',
                3 => __DIR__ . '/files/3.jpg',
            ],
            [
                1 => ['variables.input.items.0.files.0'],
                2 => ['variables.input.items.0.files.1'],
                3 => ['variables.input.items.1.files.0'],
            ],
        );
    }

    public function testCreateComplaintWithoutOrder(): void
    {
        $manualDocumentNumber = 'Whatever132';
        $complaintItemManualName = 'Product name';
        $complaintItemManualCatnum = 'Product catnum';
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'manualDocumentNumber' => $manualDocumentNumber,
                    'resolution' => ComplaintResolutionEnum::FIX,
                    'email' => self::COMPLAINT_EMAIL,
                    'items' => [
                        [
                            'quantity' => 1,
                            'description' => 'Broken!!!',
                            'manualComplaintItemName' => $complaintItemManualName,
                            'manualComplaintItemCatnum' => $complaintItemManualCatnum,
                            'files' => [null],
                        ],
                    ],
                    'deliveryAddress' => [
                        'firstName' => 'Jiří',
                        'lastName' => 'Ševčík',
                        'street' => 'První 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => new PhoneData('CZ', '+420', '123456789'),
                        'country' => 'CZ',
                    ],
                ],
            ],
            [
                1 => __DIR__ . '/files/1.jpg',
            ],
            [
                1 => ['variables.input.items.0.files.0'],
            ],
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertSame($manualDocumentNumber, $responseData['manualDocumentNumber']);
        $this->assertNull($responseData['order']);
        $this->assertNull($responseData['items'][0]['orderItem']);
        $this->assertSame($complaintItemManualCatnum, $responseData['items'][0]['catnum']);
        $this->assertSame($complaintItemManualName, $responseData['items'][0]['productName']);
    }

    public function testCreateComplaintWithoutOrderWithExistingCatnumCreatesBindingWithProduct(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $manualDocumentNumber = 'Whatever132';
        $complaintItemManualName = 'Product name';
        $complaintItemManualCatnum = $product->getCatnum();
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'manualDocumentNumber' => $manualDocumentNumber,
                    'resolution' => ComplaintResolutionEnum::FIX,
                    'email' => self::COMPLAINT_EMAIL,
                    'items' => [
                        [
                            'quantity' => 1,
                            'description' => 'Broken!!!',
                            'manualComplaintItemName' => $complaintItemManualName,
                            'manualComplaintItemCatnum' => $complaintItemManualCatnum,
                            'files' => [null],
                        ],
                    ],
                    'deliveryAddress' => [
                        'firstName' => 'Jiří',
                        'lastName' => 'Ševčík',
                        'street' => 'První 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => new PhoneData('CZ', '+420', '123456789'),
                        'country' => 'CZ',
                    ],
                ],
            ],
            [
                1 => __DIR__ . '/files/1.jpg',
            ],
            [
                1 => ['variables.input.items.0.files.0'],
            ],
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'CreateComplaint');

        $this->assertSame($product->getUuid(), $responseData['items'][0]['product']['uuid']);
        $this->assertSame($complaintItemManualName, $responseData['items'][0]['productName']);
    }

    public function testCreateComplaintWithoutOrderRequiresDocumentNumberAndItemName(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CreateComplaintMutation.graphql',
            [
                'input' => [
                    'email' => self::COMPLAINT_EMAIL,
                    'resolution' => ComplaintResolutionEnum::FIX,
                    'items' => [
                        [
                            'quantity' => 1,
                            'description' => 'Broken!!!',
                            'files' => [null],
                        ],
                    ],
                    'deliveryAddress' => [
                        'firstName' => 'Jiří',
                        'lastName' => 'Ševčík',
                        'street' => 'První 1',
                        'city' => 'Ostrava',
                        'postcode' => '71200',
                        'telephone' => new PhoneData('CZ', '+420', '123456789'),
                        'country' => 'CZ',
                    ],
                ],
            ],
            [
                1 => __DIR__ . '/files/1.jpg',
            ],
            [
                1 => ['variables.input.items.0.files.0'],
            ],
        );

        $errors = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertArrayHasKey('input.manualDocumentNumber', $errors);
        $this->assertSame(NotBlank::IS_BLANK_ERROR, $errors['input.manualDocumentNumber'][0]['code']);

        $this->assertArrayHasKey('input.items[0].manualComplaintItemName', $errors);
        $this->assertSame(NotBlank::IS_BLANK_ERROR, $errors['input.items[0].manualComplaintItemName'][0]['code']);
    }

    public function testCreateComplaintFromOrderWithWithdrawalRequestIsNotAllowed(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 1, Order::class);

        $withRequestData = $this->withdrawalRequestDataFactory->createFromArray([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'no-reply@shopsys.com',
        ]);

        $this->withdrawalRequestFacade->createOnly($order, $withRequestData);

        $response = $this->createComplaint(
            $order,
            1,
            $order->getProductItems()[0],
            2,
            $order->getProductItems()[1],
            $this->complaintResolutionEnum::FIX,
        );

        $this->assertUserError($response, 'invalid-access');
    }

    private function assertComplaintItem(
        array $expectedComplaintItem,
        int $quantity,
        int $filesCount,
        ?string $productName = null,
        ?string $productCatnum = null,
    ): void {
        $this->assertArrayHasKey('quantity', $expectedComplaintItem);
        $this->assertSame($quantity, $expectedComplaintItem['quantity']);
        $this->assertArrayHasKey('files', $expectedComplaintItem);
        $this->assertCount($filesCount, $expectedComplaintItem['files']);
        $this->assertArrayHasKey('orderItem', $expectedComplaintItem);
        $this->assertArrayHasKey('name', $expectedComplaintItem['orderItem']);
        $this->assertArrayHasKey('productName', $expectedComplaintItem);
        $this->assertSame($productName, $expectedComplaintItem['productName']);
        $this->assertArrayHasKey('catnum', $expectedComplaintItem);
        $this->assertSame($productCatnum, $expectedComplaintItem['catnum']);
    }
}
