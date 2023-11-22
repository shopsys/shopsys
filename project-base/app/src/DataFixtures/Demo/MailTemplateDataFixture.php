<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\Administrator\Mail\TwoFactorAuthenticationMail;
use App\Model\Customer\Mail\CustomerActivationMail;
use App\Model\Mail\MailTemplate;
use App\Model\Mail\MailTemplateData;
use App\Model\Mail\MailTemplateDataFactory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactoryInterface;

class MailTemplateDataFixture extends AbstractReferenceFixture implements DependentFixtureInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFactory $mailTemplateFactory
     * @param \App\Model\Mail\MailTemplateDataFactory $mailTemplateDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly MailTemplateFactoryInterface $mailTemplateFactory,
        private readonly MailTemplateDataFactory $mailTemplateDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $mailTemplateData = $this->mailTemplateDataFactory->create();
            $mailTemplateData->sendMail = true;

            $domainId = $domainConfig->getId();
            $locale = $domainConfig->getLocale();
            $mailTemplateData->subject = t(
                'Thank you for your order no. {number} placed at {date}',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $locale,
            );
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
                . '{payment_instructions}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_NEW);

            $this->createMailTemplate($manager, 'order_status_1', $mailTemplateData, $domainId);

            $mailTemplateData->sendMail = false;
            $mailTemplateData->subject = t('Order status has changed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'Your order is being processed.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_IN_PROGRESS);

            $this->createMailTemplate($manager, 'order_status_2', $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Order status has changed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'Processing your order has been finished.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_DONE);

            $this->createMailTemplate($manager, 'order_status_3', $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Order status has changed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'Your order has been cancelled.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->orderStatus = $this->getReference(OrderStatusDataFixture::ORDER_STATUS_CANCELED);

            $this->createMailTemplate($manager, 'order_status_4', $mailTemplateData, $domainId);

            $mailTemplateData->sendMail = true;
            $mailTemplateData->subject = t('Reset password request', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer.<br /><br />'
                . 'You can set a new password following this link: <a href="{new_password_url}">{new_password_url}</a>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->orderStatus = null;

            $this->createMailTemplate($manager, MailTemplate::RESET_PASSWORD_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Registration completed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />'
                . 'your registration is completed. <br />'
                . 'Name: {first_name} {last_name}<br />'
                . 'Email: {email}<br />'
                . 'E-shop link: {url}<br />'
                . 'Log in page: {login_page}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->createMailTemplate($manager, MailTemplate::REGISTRATION_CONFIRM_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Personal information overview - {domain}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer, <br /><br />
            based on your email {email}, we are sending you a link to your personal details. By clicking on the link below, you will be taken to a page listing all the<br/>
            personal details which we have in evidence in our online store {domain}. 
            <br/><br/>
            To overview your personal information please click here - {url} <br/>
            The link is valid for next 24 hours.<br/>
            Best Regards <br/><br/>
            team of {domain}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->createMailTemplate($manager, MailTemplate::PERSONAL_DATA_ACCESS_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Personal information export - {domain}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer, <br/><br/>
based on your email {email}, we are sending you a link where you can download your personal details registered on our online store in readable format. Clicking on the link will take you to a page where you’ll be able to download these informations, which we have in evidence in our online store {domain}. 
<br/><br/>
To download your personal information please click here - {url}<br/> 
The link is valid for next 24 hours.
<br/><br/>
Best regards<br/>
team of {domain}
', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->createMailTemplate($manager, MailTemplate::PERSONAL_DATA_EXPORT_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Registration completion', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Dear customer,<br /><br />you can finish registration and set new password via this link: <a href="{activation_url}">{activation_url}</a>', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);

            $this->createMailTemplate($manager, CustomerActivationMail::CUSTOMER_ACTIVATION_NAME, $mailTemplateData, $domainId);

            $mailTemplateData->subject = t('Authentication code', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $mailTemplateData->body = t('Authentication code for two factor authentication: {authentication_code}', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
            $this->createMailTemplate($manager, TwoFactorAuthenticationMail::TWO_FACTOR_AUTHENTICATION_CODE, $mailTemplateData, $domainId);
        }
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     * @param string $name
     * @param \App\Model\Mail\MailTemplateData $mailTemplateData
     * @param int $domainId
     */
    private function createMailTemplate(
        ObjectManager $manager,
        string $name,
        MailTemplateData $mailTemplateData,
        int $domainId,
    ): void {
        $repository = $manager->getRepository(MailTemplate::class);

        $mailTemplate = $repository->findOneBy([
            'name' => $name,
            'domainId' => $domainId,
        ]);

        $mailTemplateData->body = <<<EOT
            <div style="box-sizing: border-box; padding: 10px;">
                <div class="gjs-text-ckeditor">{$mailTemplateData->body}</div>
            </div>
        EOT;

        if ($mailTemplate === null) {
            $mailTemplate = $this->mailTemplateFactory->create($name, $domainId, $mailTemplateData);
        } else {
            $mailTemplate->edit($mailTemplateData);
        }

        $manager->persist($mailTemplate);
        $manager->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [
            TransportDataFixture::class,
            PaymentDataFixture::class,
            OrderStatusDataFixture::class,
        ];
    }
}
