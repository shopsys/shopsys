<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateData;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface;

class MailTemplateDataFixture extends AbstractReferenceFixture
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface
     */
    protected $mailTemplateFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface
     */
    protected $mailTemplateDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface $mailTemplateFactory
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateDataFactoryInterface $mailTemplateDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        MailTemplateFactoryInterface $mailTemplateFactory,
        MailTemplateDataFactoryInterface $mailTemplateDataFactory,
        Domain $domain
    ) {
        $this->mailTemplateFactory = $mailTemplateFactory;
        $this->mailTemplateDataFactory = $mailTemplateDataFactory;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $mailTemplateData = $this->mailTemplateDataFactory->create();
        $mailTemplateData->sendMail = true;

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();
            $mailTemplateData->subject = t('Thank you for your order no. {number} placed at {date}', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer,<br /><br />'
                . 'Your order has been placed successfully.<br /><br />'
                . 'You will be contacted when the order state changes.<br />'
                . 'Order number: {number} <br />'
                . 'Date and time of creation: {date} <br />'
                . 'E-shop link: {url} <br />'
                . 'Order detail link: {order_detail_url} <br />'
                . 'Shipping: {transport} <br />'
                . 'Payment: {payment} <br />'
                . 'Total price including VAT: {total_price} <br />'
                . 'Billing address:<br /> {billing_address} <br />'
                . 'Delivery address: {delivery_address} <br />'
                . 'Note: {note} <br />'
                . 'Products: {products} <br />'
                . '{transport_instructions} <br />'
                . '{payment_instructions}', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, 'order_status_1', $mailTemplateData, $domainId);

            $mailTemplateData->sendMail = false;
            $mailTemplateData->subject = t('Order status has changed', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'Your order is being processed.', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, 'order_status_2', $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Order status has changed', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'Processing your order has been finished.', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, 'order_status_3', $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Order status has changed', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'Your order has been cancelled.', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, 'order_status_4', $mailTemplateData, $domainId);

            $mailTemplateData->sendMail = true;
            $mailTemplateData->subject = t('Reset password request', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer.<br /><br />'
                . 'You can set a new password following this link: <a href="{new_password_url}">{new_password_url}</a>', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, MailTemplate::RESET_PASSWORD_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Registration completed', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'your registration is completed. <br />'
                . 'Name: {first_name} {last_name}<br />'
                . 'Email: {email}<br />'
                . 'E-shop link: {url}<br />'
                . 'Log in page: {login_page}', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, MailTemplate::REGISTRATION_CONFIRM_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Personal information overview - {domain}', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />
            based on your email {email}, we are sending you a link to your personal details. By clicking on the link below, you will be taken to a page listing all the<br/>
            personal details which we have in evidence in our online store {domain}. 
            <br/><br/>
            To overview your personal information please click here - {url} <br/>
            The link is valid for next 24 hours.<br/>
            Best Regards <br/><br/>
            team of {domain}', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, MailTemplate::PERSONAL_DATA_ACCESS_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Personal information export - {domain}', [], 'dataFixtures', $locale);
            $mailTemplateData->body = t('Dear customer, <br/><br/>
based on your email {email}, we are sending you a link where you can download your personal details registered on our online store in readable format. Clicking on the link will take you to a page where you’ll be able to download these informations, which we have in evidence in our online store {domain}. 
<br/><br/>
To download your personal information please click here - {url}<br/> 
The link is valid for next 24 hours.
<br/><br/>
Best regards<br/>
team of {domain}
', [], 'dataFixtures', $locale);

            $this->createMailTemplate($manager, MailTemplate::PERSONAL_DATA_EXPORT_NAME, $mailTemplateData, $domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $manager
     * @param mixed $name
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData $mailTemplateData
     * @param int $domainId
     */
    protected function createMailTemplate(
        ObjectManager $manager,
        $name,
        MailTemplateData $mailTemplateData,
        int $domainId
    ) {
        $repository = $manager->getRepository(MailTemplate::class);

        $mailTemplate = $repository->findOneBy([
            'name' => $name,
            'domainId' => $domainId,
        ]);

        if ($mailTemplate === null) {
            $mailTemplate = $this->mailTemplateFactory->create($name, $domainId, $mailTemplateData);
        } else {
            $mailTemplate->edit($mailTemplateData);
        }

        $manager->persist($mailTemplate);
        $manager->flush($mailTemplate);
    }
}
