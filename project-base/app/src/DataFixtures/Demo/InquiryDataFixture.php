<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use DateTimeImmutable;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryDataFactory;
use Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade;

class InquiryDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryFacade $inquiryFacade
     * @param \Shopsys\FrameworkBundle\Model\Inquiry\InquiryDataFactory $inquiryDataFactory
     */
    public function __construct(
        protected readonly InquiryFacade $inquiryFacade,
        protected readonly InquiryDataFactory $inquiryDataFactory,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $inquiryProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '45');

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Mark';
        $inquiryData->lastName = 'Spencer';
        $inquiryData->email = 'mark.spencer@example.com';
        $inquiryData->telephone = '111222333';
        $inquiryData->companyName = 'Weyland-Yutani Corporation';
        $inquiryData->companyNumber = '12345678';
        $inquiryData->companyTaxNumber = 'CZ12345678';
        $inquiryData->note = 'Hello, I am interested in learning more about the specifications and pricing for the product. Could you provide detailed information regarding the material quality, warranty period, and the estimated delivery time if we place a bulk order of around 500 units? Additionally, are there any discounts available for bulk purchases or long-term partnerships? We’re evaluating several suppliers, and I’d like to have this information to finalize our decision. Looking forward to your response. Thank you.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-03 10:28:23');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Laura';
        $inquiryData->lastName = 'Davis';
        $inquiryData->email = 'laura.davis@example.com';
        $inquiryData->telephone = '444555666';
        $inquiryData->companyName = 'Aperture Science';
        $inquiryData->companyNumber = '98765432';
        $inquiryData->companyTaxNumber = 'US98765432';
        $inquiryData->note = 'Can I get more details?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-03 7:51:10');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Daniel';
        $inquiryData->lastName = 'Clark';
        $inquiryData->email = 'daniel.clark@example.com';
        $inquiryData->telephone = '777888999';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'What is the cost for personal use?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-03 7:55:14');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Sarah';
        $inquiryData->lastName = 'Johnson';
        $inquiryData->email = 'sarah.johnson@example.com';
        $inquiryData->telephone = '123123123';
        $inquiryData->companyName = 'Cyberdyne Systems';
        $inquiryData->companyNumber = '11223344';
        $inquiryData->companyTaxNumber = 'UK11223344';
        $inquiryData->note = 'Looking for AI powered solution.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 8:34:17');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Kevin';
        $inquiryData->lastName = 'Miller';
        $inquiryData->email = 'kevin.miller@example.com';
        $inquiryData->telephone = '555666777';
        $inquiryData->companyName = 'Umbrella Corporation';
        $inquiryData->companyNumber = '55667788';
        $inquiryData->companyTaxNumber = 'DE55667788';
        $inquiryData->note = 'Interested in product.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 8:19:42');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Jessica';
        $inquiryData->lastName = 'Adams';
        $inquiryData->email = 'jessica.adams@example.com';
        $inquiryData->telephone = '888999000';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Can I order without a company?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 8:51:07');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Brian';
        $inquiryData->lastName = 'Wilson';
        $inquiryData->email = 'brian.wilson@example.com';
        $inquiryData->telephone = '999888777';
        $inquiryData->companyName = 'Stark Industries';
        $inquiryData->companyNumber = '66778899';
        $inquiryData->companyTaxNumber = 'US66778899';
        $inquiryData->note = 'Can you provide details on the product?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 9:08:42');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Emma';
        $inquiryData->lastName = 'Thompson';
        $inquiryData->email = 'emma.thompson@example.com';
        $inquiryData->telephone = '777666555';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Just inquiring for personal use.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 9:17:39');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Oliver';
        $inquiryData->lastName = 'Brown';
        $inquiryData->email = 'oliver.brown@example.com';
        $inquiryData->telephone = '222333444';
        $inquiryData->companyName = 'Black Mesa';
        $inquiryData->companyNumber = '44556677';
        $inquiryData->companyTaxNumber = 'US44556677';
        $inquiryData->note = 'Do you sell in large numbers?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 9:18:22');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Hannah';
        $inquiryData->lastName = 'White';
        $inquiryData->email = 'hannah.white@example.com';
        $inquiryData->telephone = '555444333';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'I don’t have a company, is that okay?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:04:53');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Lucas';
        $inquiryData->lastName = 'King';
        $inquiryData->email = 'lucas.king@example.com';
        $inquiryData->telephone = '111999888';
        $inquiryData->companyName = 'Nakatomi Corporation';
        $inquiryData->companyNumber = '33445566';
        $inquiryData->companyTaxNumber = 'JP33445566';
        $inquiryData->note = 'Does it handle building security systems?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:14:41');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Sophie';
        $inquiryData->lastName = 'Robinson';
        $inquiryData->email = 'sophie.robinson@example.com';
        $inquiryData->telephone = '333222111';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Interested in individual pricing.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:02:05');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Nathan';
        $inquiryData->lastName = 'Green';
        $inquiryData->email = 'nathan.green@example.com';
        $inquiryData->telephone = '888777666';
        $inquiryData->companyName = 'Oscorp Industries';
        $inquiryData->companyNumber = '55667744';
        $inquiryData->companyTaxNumber = 'US55667744';
        $inquiryData->note = 'Can you provide more details?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:06:42');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Megan';
        $inquiryData->lastName = 'Stewart';
        $inquiryData->email = 'megan.stewart@example.com';
        $inquiryData->telephone = '999888111';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Looking to order as an individual.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:42:54');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'David';
        $inquiryData->lastName = 'Scott';
        $inquiryData->email = 'david.scott@example.com';
        $inquiryData->telephone = '444555666';
        $inquiryData->companyName = 'Omni Consumer Products';
        $inquiryData->companyNumber = '77665544';
        $inquiryData->companyTaxNumber = 'US77665544';
        $inquiryData->note = 'Could I get pricing for use in drones?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:53:05');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Grace';
        $inquiryData->lastName = 'Lewis';
        $inquiryData->email = 'grace.lewis@example.com';
        $inquiryData->telephone = '333444555';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Do you offer discounts for individuals?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:30:35');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'James';
        $inquiryData->lastName = 'Parker';
        $inquiryData->email = 'james.parker@example.com';
        $inquiryData->telephone = '222333111';
        $inquiryData->companyName = 'InGen Corporation';
        $inquiryData->companyNumber = '55443322';
        $inquiryData->companyTaxNumber = 'US55443322';
        $inquiryData->note = 'Can you provide a quote?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:38:27');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Anna';
        $inquiryData->lastName = 'Walker';
        $inquiryData->email = 'anna.walker@example.com';
        $inquiryData->telephone = '555666777';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'What’s the pricing structure for individual customers?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:30:44');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Liam';
        $inquiryData->lastName = 'Bell';
        $inquiryData->email = 'liam.bell@example.com';
        $inquiryData->telephone = '777888999';
        $inquiryData->companyName = 'Shinra Electric Power Company';
        $inquiryData->companyNumber = '99887766';
        $inquiryData->companyTaxNumber = 'JP99887766';
        $inquiryData->note = 'Can I get information?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:58:20');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Emily';
        $inquiryData->lastName = 'Brooks';
        $inquiryData->email = 'emily.brooks@example.com';
        $inquiryData->telephone = '999111222';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'I would like to inquire as an individual.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:33:58');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Joshua';
        $inquiryData->lastName = 'Morgan';
        $inquiryData->email = 'joshua.morgan@example.com';
        $inquiryData->telephone = '555999444';
        $inquiryData->companyName = 'Vault-Tec Corporation';
        $inquiryData->companyNumber = '22334455';
        $inquiryData->companyTaxNumber = 'US22334455';
        $inquiryData->note = 'I am interested in your vault systems.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:56:50');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Isabella';
        $inquiryData->lastName = 'Murphy';
        $inquiryData->email = 'isabella.murphy@example.com';
        $inquiryData->telephone = '222888777';
        $inquiryData->companyName = 'Tyrell Corporation';
        $inquiryData->companyNumber = '77665588';
        $inquiryData->companyTaxNumber = 'US77665588';
        $inquiryData->note = 'Can you provide more details?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:48:56');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Mason';
        $inquiryData->lastName = 'Reed';
        $inquiryData->email = 'mason.reed@example.com';
        $inquiryData->telephone = '444777666';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'I want to know if you offer individual pricing.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:18:35');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Victoria';
        $inquiryData->lastName = 'Patterson';
        $inquiryData->email = 'victoria.patterson@example.com';
        $inquiryData->telephone = '999777111';
        $inquiryData->companyName = 'Arasaka Corporation';
        $inquiryData->companyNumber = '99887744';
        $inquiryData->companyTaxNumber = 'JP99887744';
        $inquiryData->note = 'Can you provide details about incorporating into cyber software?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:55:34');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Ryan';
        $inquiryData->lastName = 'Mitchell';
        $inquiryData->email = 'ryan.mitchell@example.com';
        $inquiryData->telephone = '888555666';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'I am interested in an individual product purchase.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:07:41');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Zoe';
        $inquiryData->lastName = 'Turner';
        $inquiryData->email = 'zoe.turner@example.com';
        $inquiryData->telephone = '777444333';
        $inquiryData->companyName = 'Faro Automated Solutions';
        $inquiryData->companyNumber = '33445599';
        $inquiryData->companyTaxNumber = 'US33445599';
        $inquiryData->note = 'Could you provide more info on the technology?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:17:27');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Dylan';
        $inquiryData->lastName = 'Evans';
        $inquiryData->email = 'dylan.evans@example.com';
        $inquiryData->telephone = '999555222';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Is there an option for private customers?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:10:56');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Charlotte';
        $inquiryData->lastName = 'Hughes';
        $inquiryData->email = 'charlotte.hughes@example.com';
        $inquiryData->telephone = '123333444';
        $inquiryData->companyName = 'Atlas Corporation';
        $inquiryData->companyNumber = '77665522';
        $inquiryData->companyTaxNumber = 'US77665522';
        $inquiryData->note = 'Interested in details about development.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 09:25:41');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Benjamin';
        $inquiryData->lastName = 'Wood';
        $inquiryData->email = 'benjamin.wood@example.com';
        $inquiryData->telephone = '333111999';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'What’s the pricing for non-corporate orders?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:04:51');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Lily';
        $inquiryData->lastName = 'Foster';
        $inquiryData->email = 'lily.foster@example.com';
        $inquiryData->telephone = '123555666';
        $inquiryData->companyName = 'Fragile Express';
        $inquiryData->companyNumber = '66778899';
        $inquiryData->companyTaxNumber = 'US66778899';
        $inquiryData->note = 'Can you provide more information about the product line?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 9:22:14');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Gabriel';
        $inquiryData->lastName = 'Griffin';
        $inquiryData->email = 'gabriel.griffin@example.com';
        $inquiryData->telephone = '999444333';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Do you offer products for individual use?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:15:41');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Sophia';
        $inquiryData->lastName = 'Evans';
        $inquiryData->email = 'sophia.evans@example.com';
        $inquiryData->telephone = '555222888';
        $inquiryData->companyName = 'Abstergo Industries';
        $inquiryData->companyNumber = '44556677';
        $inquiryData->companyTaxNumber = 'US44556677';
        $inquiryData->note = 'Interested in more details on your memory research products.';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 10:18:11');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Ethan';
        $inquiryData->lastName = 'Bailey';
        $inquiryData->email = 'ethan.bailey@example.com';
        $inquiryData->telephone = '777888222';
        $inquiryData->companyName = null;
        $inquiryData->companyNumber = null;
        $inquiryData->companyTaxNumber = null;
        $inquiryData->note = 'Do you have custom pricing for individuals?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 17:42:37');
        $this->inquiryFacade->create($inquiryData);

        $inquiryData = $this->inquiryDataFactory->create();
        $inquiryData->firstName = 'Madison';
        $inquiryData->lastName = 'Perry';
        $inquiryData->email = 'madison.perry@example.com';
        $inquiryData->telephone = '888444555';
        $inquiryData->companyName = 'Halcyon Holdings Corporation';
        $inquiryData->companyNumber = '99887755';
        $inquiryData->companyTaxNumber = 'US99887755';
        $inquiryData->note = 'Can you provide more info?';
        $inquiryData->product = $inquiryProduct;
        $inquiryData->createdAt = new DateTimeImmutable('2024-09-02 19:12:47');
        $this->inquiryFacade->create($inquiryData);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ProductDataFixture::class,
        ];
    }
}
