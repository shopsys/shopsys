# Adding a New Email Template

In this cookbook, we will add a new email template to alert the customer when his password is changed.

We want to send an email to the user when his password is reset.
This email should be configurable in administration, and we should be able to personalize email – we want to be able to include the customer email and full name into the email.

## New Mail class

The first step to creating a new email template is creating a class to create a `MessageData` object that can then be sent with `Mailer`.

```php
declare(strict_types=1);

namespace App\Component\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplate;
use Shopsys\FrameworkBundle\Model\Mail\MessageData;
use Shopsys\FrameworkBundle\Model\Mail\MessageFactoryInterface;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSetting;

class PasswordChangedMail implements MessageFactoryInterface
{
    // unique identifier of email template
    public const MAIL_TEMPLATE_NAME = 'password_changed';

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }


    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $template
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Mail\MessageData
     */
    public function createMessage(MailTemplate $template, $customerUser): MessageData
    {
        return new MessageData(
            $customerUser->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $customerUser->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $customerUser->getDomainId())
        );
    }
}
```

## Add a new email template to the database

We need to populate the database with some data to test the email template.  
To do that, we can create a new database migration.
Although it's possible to use DataFixtures, we recommend using migrations for this task.
DataFixtures are not run during production deployment, and it is possible that the missing email template will cause errors.

```php
namespace App\Migrations;

use App\Component\Mail\PasswordChangedMail;
use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class VersionXXXXXXXXXXXXXX extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $domainLocale = $this->getDomainLocale($domainId);

             $this->sql(
                'INSERT INTO mail_templates (name, domain_id, subject, body, send_mail) VALUES (:mailTemplateName, :domainId, :subject, :body, :sendMail)',
                [
                    'mailTemplateName' => PasswordChangedMail::MAIL_TEMPLATE_NAME,
                    'domainId' => $domainId,
                    'subject' => t('Your password has changed', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainLocale),
                    'body' => t('Dear {fullname},<br/><br/>
                        We wanted to let you know that your password has changed.
                        <br/><br/>
                        If you did not perform this action, you can recover access by entering {email} into the form at {password_reset_url}
                        <br/><br/>
                        Best regards
                        ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
                    'sendMail' => true,
                ],
            );
        }
    }
```

You can see we used several variable placeholders in this template (`{fullname}`, `{email}`, and `{password_reset_url}`).
Right now, they are treated as plain text.
We will allow replacing them with real values in the next step.

!!! note

    In the example above, we translated the email subject and body.<br>
    Don't forget to [dump translations](../introduction/console-commands-for-application-management-phing-targets.md#translations-dump).

## Replacing variables with values

We want to be able to use several variables and replace them with real values when the email should be sent.
To do that, we update the previously created `PasswordChangedMail` class.

First, we add constants representing each variable to be able to reference them in code.

```diff
 class PasswordChangedMail implements MessageFactoryInterface
 {
     // unique identifier of email template
     public const MAIL_TEMPLATE_NAME = 'password_changed';

+    public const VARIABLE_FULLNAME = '{fullname}';
+    public const VARIABLE_EMAIL = '{email}';
+    public const VARIABLE_PASSWORD_RESET_URL = '{password_reset_url}';
```

Replacing variables is internally supported in the `MessageData` class in our `PasswordChangedMail`.
We just need to pass an array of replacements (in the format `{variable} => realValue`).

```diff
// class App\Component\Mail\PasswordChangedMail

    // DomainRouterFactory is necessary to be able to generate a URL to reset the password form
+   /**
+    * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
+    */
+   protected $domainRouterFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
+    * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
-   public function __construct(Setting $setting)
+   public function __construct(Setting $setting, DomainRouterFactory $domainRouterFactory)
    {
        $this->setting = $setting;
+       $this->domainRouterFactory = $domainRouterFactory;
    }


    public function createMessage(MailTemplate $template, $customerUser): MessageData
    {
        return new MessageData(
            $customerUser->getEmail(),
            $template->getBccEmail(),
            $template->getBody(),
            $template->getSubject(),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL, $customerUser->getDomainId()),
            $this->setting->getForDomain(MailSetting::MAIN_ADMIN_MAIL_NAME, $customerUser->getDomainId())
+           $this->getBodyVariablesReplacements($customerUser),
+           $this->getSubjectVariablesReplacements($customerUser)
        );
    }
```

and corresponding methods can look like this

```php
// class App\Component\Mail\PasswordChangedMail

   /**
    * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
    * @return array
    */
   private function getSubjectVariablesReplacements(CustomerUser $customerUser): array
   {
       return [
           self::VARIABLE_FULLNAME => htmlspecialchars($customerUser->getFullName(), ENT_QUOTES),
       ];
   }

   /**
    * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
    * @return array
    */
   private function getBodyVariablesReplacements(CustomerUser $customerUser): array
   {
       $router = $this->domainRouterFactory->getRouter($customerUser->getDomainId());

       return [
           self::VARIABLE_FULLNAME => htmlspecialchars($customerUser->getFullName(), ENT_QUOTES),
           self::VARIABLE_EMAIL => htmlspecialchars($customerUser->getEmail(), ENT_QUOTES),
           self::VARIABLE_PASSWORD_RESET_URL => $router->generate('front_registration_reset_password', [], UrlGeneratorInterface::ABSOLUTE_URL),
       ];
   }
```

!!! note

    In this example, we're intentionally replacing all defined variables in the email body, but in the subject, only the customer full name is replaced.

!!! warning

    Replacements (real values) for the variables are, most of the time, some user-entered values.<br>
    It's crucial to escape these values properly!

## How to add custom variables to an existing mail template

You can append them by calling parent method like this

```php
/**
 * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables
 */
protected function createOrderStatusMailTemplateVariables(): MailTemplateVariables
{
    return parent::createOrderStatusMailTemplateVariables()
        ->addVariable(OrderMail::VARIABLE_TRACKING_INSTRUCTIONS, t('Tracking instructions'), MailTemplateVariables::CONTEXT_BODY);
}
```

## Sending email

Now, when the template is stored in the database, and we are properly replacing variables, we are ready to send this email when the user enters a new password after the reset password process.

To simplify things, we add sending an email directly into `CustomerPasswordController::setNewPasswordAction()`.  
In your application, consider a better place.

```diff
// class App\Controller\Front\CustomerPasswordController

    /*
     * @param \Symfony\Component\HttpFoundation\Request $request
+    * @param \App\Component\Mail\PasswordChangedMail $passwordChangedMail
+    * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateFacade $mailTemplateFacade
+    * @param \Shopsys\FrameworkBundle\Model\Mail\Mailer $mailer
+    * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade $uploadedFileFacade
+    * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setNewPasswordAction(
        Request $request,
+       PasswordChangedMail $passwordChangedMail,
+       MailTemplateFacade $mailTemplateFacade,
+       Mailer $mailer,
+       UploadedFileFacade $uploadedFileFacade
+   ) {

    // ...

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $newPassword = $formData['newPassword'];

            try {
                $customerUser = $this->customerUserPasswordFacade->setNewPassword($email, $this->domain->getId(), $hash, $newPassword);

+               $mailTemplate = $mailTemplateFacade->get(PasswordChangedMail::MAIL_TEMPLATE_NAME, $customerUser->getDomainId());
+               $messageData = $passwordChangedMail->createMessage($mailTemplate, $customerUser);
+               $messageData->attachments = $uploadedFileFacade->getUploadedFilesByEntity($mailTemplate);
+               $mailer->send($messageData);

```

And you need to register the previously created class `PasswordChangedMail` into your `services.yaml` file, as it should be autowired.

```diff
# config/services.yaml

services:

    # ...

+   App\Component\Mail\PasswordChangedMail: ~
```

Go ahead and try to reset a customer's password.
You will receive an email notification about the changed password for the account.

## Make the mail template configurable in administration

One of the requirements was to be able to edit the template in administration.
Let's make it possible.

Shopsys Platform made this task really easy.  
You just need to define variables, their labels for the form, allowed usage, and whether they are required or not.

This configuration is made in PHP to ease translating values.
We create a new provider class.

```php
declare(strict_types=1);

namespace App\Component\Mail;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateVariables;

class PasswordChangedMailTemplateVariablesProvider
{
    public function create(): MailTemplateVariables
    {
        // first argument is Mail Template readable name
        $mailTemplateVariables = new MailTemplateVariables(t('Password was changed'));

        $mailTemplateVariables->addVariable(
            PasswordChangedMail::VARIABLE_EMAIL, // reuse already defined variable placeholders
            t('Customer email'), // readable name of the variable
            MailTemplateVariables::CONTEXT_BODY, // variable takes place in body only
            MailTemplateVariables::REQUIRED_BODY // variable is required in body
        );

        $mailTemplateVariables->addVariable(
            PasswordChangedMail::VARIABLE_PASSWORD_RESET_URL,
            t('Reset password link'),
            MailTemplateVariables::CONTEXT_BODY
        );

        // by default, the variable is not required and can be used in both subject and body
        $mailTemplateVariables->addVariable(
            PasswordChangedMail::VARIABLE_FULLNAME,
            t('Customer full name')
        );

        return $mailTemplateVariables;
    }
}
```

Each variable is added with the `addVariable(string $variable, string $label, $context, $required)` method.

-   `$variable` is a variable placeholder
-   `$label` is a readable name to describe the meaning of the variable to the user
-   `$context` defines where the variable is applicable and can have one of these values:
    -   `MailTemplateVariables::CONTEXT_BOTH` – variable can take place in the subject and body (default)
    -   `MailTemplateVariables::CONTEXT_BODY` - variable can take place in the body only
    -   `MailTemplateVariables::CONTEXT_SUBJECT` - variable can take place in the subject only
-   `$required` defines where the variable is required and can have one of these values:
    -   `MailTemplateVariables::REQUIRED_NOWHERE` - variable is optional (default)
    -   `MailTemplateVariables::REQUIRED_BOTH` - variable has to be present in the body and in the subject
    -   `MailTemplateVariables::REQUIRED_BODY` - variable has to be present in the body
    -   `MailTemplateVariables::REQUIRED_SUBJECT` - variable has to be present in the subject

When we have the variables ready, the last step is to register variables with the proper mail template.  
This can be done in `config/services.yaml` file

```yaml
Shopsys\FrameworkBundle\Model\Mail\MailTemplateConfiguration:
    calls:
        - method: addMailTemplateVariables
          arguments:
              - !php/const App\Component\Mail\PasswordChangedMail::MAIL_TEMPLATE_NAME
              - '@=service("App\\Component\\Mail\\PasswordChangedMailTemplateVariablesProvider").create()'
```

## Conclusion

Now, in your database is a new email template, and an email from this template is sent to the user whenever he resets his password.
This template can be easily changed from the administration.
