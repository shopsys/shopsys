# [Upgrade from v8.1.0-dev to v9.0.0-dev](https://github.com/shopsys/shopsys/compare/HEAD...9.0)

This guide contains instructions to upgrade from version v8.1.0-dev to v9.0.0-dev.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]

- Before doing any other upgrade instructions, you have to upgrade your application to Symfony Flex as some file paths are changed.
  Follow upgrade instructions in the [separate article](./upgrade-instructions-for-symfony-flex.md) ([#1492](https://github.com/shopsys/shopsys/pull/1492))
  All following upgrade instructions are written for upgraded application with Symfony Flex

### Infrastructure
- update your `kubernetes/deployments/webserver-php-fpm.yml` file: ([#1368](https://github.com/shopsys/shopsys/pull/1368))
    ```diff
    -   command: ["sh", "-c", "cd /var/www/html && ./phing db-create dirs-create db-demo product-search-recreate-structure product-search-export-products grunt error-pages-generate warmup"]
    +   command: ["sh", "-c", "cd /var/www/html && ./phing -D production.confirm.action=y db-create dirs-create db-demo product-search-recreate-structure product-search-export-products grunt error-pages-generate warmup"]
    ```
- check all the phing targets that depend on the new `production-protection` target
    - if you use any of the targets in your automated build scripts in production environment, you need to pass the confirmation to the phing using `-D production.confirm.action=y`

### Application

- update your twig files ([#1284](https://github.com/shopsys/shopsys/pull/1284/)):
    - `templates/Front/Content/Product/list.html.twig`
        - remove: `{% import 'Front/Content/Product/productListMacro.html.twig' as productList %}`
    - `templates/Front/Content/Product/listByBrand.html.twig`
        - remove: `{% import 'Front/Content/Product/productListMacro.html.twig' as productList %}`
    - `templates/Front/Content/Product/productListMacro.html.twig`
        - remove: `{% import 'Front/Inline/Product/productFlagsMacro.html.twig' as productFlags %}`
    - `templates/Front/Content/Product/search.html.twig`
        - remove: `{% import 'Front/Content/Product/productListMacro.html.twig' as productList %}`
    - check your templates if you are extending or importing any of the following templates as imports of unused macros were removed from them:
        - `templates/Admin/Content/Article/detail.html.twig`
        - `templates/Admin/Content/Brand/detail.html.twig`
        - `templates/Admin/Content/Category/detail.html.twig`
        - `templates/Admin/Content/Product/detail.html.twig`
- add [`app/getEnvironment.php`](https://github.com/shopsys/shopsys/blob/9.0/project-base/app/getEnvironment.php) file to your project ([#1368](https://github.com/shopsys/shopsys/pull/1368))
- add optional [Frontend API](https://github.com/shopsys/shopsys/blob/9.0/docs/frontend-api/introduction-to-frontend-api.md) to your project ([#1445](https://github.com/shopsys/shopsys/pull/1445), [#1486](https://github.com/shopsys/shopsys/pull/1486), [#1493](https://github.com/shopsys/shopsys/pull/1493), [#1489](https://github.com/shopsys/shopsys/pull/1489)):
    - add `shopsys/frontend-api` dependency with `composer require shopsys/frontend-api`
    - register necessary bundles in `config/bundles.php`
        ```diff
            Shopsys\FormTypesBundle\ShopsysFormTypesBundle::class => ['all' => true],
        +   Shopsys\FrontendApiBundle\ShopsysFrontendApiBundle::class => ['all' => true],
        +   Overblog\GraphQLBundle\OverblogGraphQLBundle::class => ['all' => true],
        +   Overblog\GraphiQLBundle\OverblogGraphiQLBundle::class => ['dev' => true],
            Shopsys\GoogleCloudBundle\ShopsysGoogleCloudBundle::class => ['all' => true],
        ```
    - add new route file [`config/routes/frontend-api.yml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/routes/frontend-api.yml) from GitHub
    - add new route file [`config/routes/dev/frontend-api-graphiql.yaml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/routes/dev/frontend-api-graphiql.yaml) from GitHub
    - copy [type definitions from Github](https://github.com/shopsys/shopsys/tree/9.0/project-base/config/graphql/types) into `config/graphql/types/` folder
    - copy necessary configuration [shopsys_frontend_api.yml from Github](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/packages/shopsys_frontend_api.yml) to `config/packages/shopsys_frontend_api.yml`
    - copy [tests for FrontendApiBundle from Github](https://github.com/shopsys/shopsys/tree/9.0/project-base/tests/FrontendApiBundle) to your `tests` folder
    - enable Frontend API for desired domains in `config/parameters_common.yml` file
    for example
        ```diff
          parmeters:
              # ...
        +     shopsys.frontend_api.domains:
        +         - 1
        +         - 2
    - update your `easy-coding-standard.yml` file:
        - add `'*/tests/FrontendApiBundle/Functional/Image/ProductImagesTest.php'` in `ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff` part
- removed unused `block domain` defined in `Admin/Content/Slider/edit.html.twig` ([#1437](https://github.com/shopsys/shopsys/pull/1437))
    - in case you are using this block of code you should copy it into your project (see PR mentioned above for more details)
- add access denied url to `config/packages/security.yml` for users which are not granted with access to the requested page ([#1504](https://github.com/shopsys/shopsys/pull/1504))
    ```diff
         administration:
             pattern: ^/(admin/|efconnect|elfinder)
             user_checker: Shopsys\FrameworkBundle\Model\Security\AdministratorChecker
             anonymous: ~
             provider: administrators
             logout_on_user_change: true
    +        access_denied_url: "/admin/access-denied/"
             form_login:
    ```
    - add new customized route `admin_access_denied` in `RouteConfigCustomization`
    ```diff
         ->customizeByRouteName('admin_domain_list', function (RouteConfig $config) {
             if ($this->isSingleDomain()) {
                $config->skipRoute('Domain list in administration is not available when only 1 domain exists.');
             }
    +    })
    +    ->customizeByRouteName('admin_access_denied', function (RouteConfig $config) {
    +        $config->changeDefaultRequestDataSet('This route serves as "access_denied_url" (see security.yml) and always redirects to a referer (or dashboard).')
    +           ->setExpectedStatusCode(302);
             });
        }
    ```
    - change expected status code for testing superadmin routes from code `404` to `302`
    ```diff
         if (preg_match('~^admin_(superadmin_|translation_list$)~', $info->getRouteName())) {
             $config->changeDefaultRequestDataSet('Only superadmin should be able to see this route.')
    -           ->setExpectedStatusCode(404);
    +           ->setExpectedStatusCode(302);
    ```
    ```diff
        ->customizeByRouteName('admin_administrator_edit', function (RouteConfig $config) {
            $config->changeDefaultRequestDataSet('Standard admin is not allowed to edit superadmin (with ID 1)')
    -           ->setExpectedStatusCode(404);
    +           ->setExpectedStatusCode(302);
    ```
- update your project to use refactored FileUpload functionality with added support for multiple files ([#1531])(https://github.com/shopsys/shopsys/pull/1531/)
    - there were changes in framework classes, styles and scripts so update your project appropriately:
        - `UploadedFileEntityConfigNotFoundException::getEntityClassOrName()` has been removed
        - `UploadedFileFacade::findUploadedFileByEntity()` and `UploadedFileFacade::getUploadedFileByEntity()` has been removed, use `UploadedFileFacade::getUploadedFilesByEntity()` instead
        - `UploadedFileFacade::uploadFile()` is now protected, use `UploadedFileFacade::manageFiles()` instead
        - `UploadedFileRepository::findUploadedFileByEntity()` and `UploadedFileRepository::getUploadedFileByEntity()` has been removed, use `UploadedFileRepository::getUploadedFilesByEntity()` or `UploadedFileRepository::getAllUploadedFilesByEntity()` instead
        - `UploadedFileFacade::hasUploadedFile()` has been removed
        - `UploadedFileFacade::deleteUploadedFileByEntity()` has been removed use `UploadedFileFacade::deleteFiles()` instead
        - `src/Resources/scripts/admin/mailTemplate.attachmentDelete.js` has been removed
        - `UploadedFileExtension::getUploadedFileByEntity()` and `UploadedFileExtension::hasUploadedFile()` has been removed including its Twig functions `hasUploadedFile` and `getUploadedFile`
        - `UploadedFileExtension::getUploadedFileUrl()` and `UploadedFileExtension::getUploadedFilePreviewHtml()` now expect `UploadedFile` instead of entity that applies also for their Twig functions `uploadedFileUrl` and `uploadedFilePreview`
        - `UploadedFile::setTemporaryFilename()` does not longer accept null
        - `FileUploadType` now requires options `entity`, `file_entity_class` and `file_type` see [documentation](https://docs.shopsys.com/en/9.0/introduction/using-form-types/#fileuploadtype) for more info
        - `MailTemplateData::attachment()` and `MailTemplateData::deleteAttachment()` has been replaced by `MailTemplateData::attachments()` that is of type `\Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData`
        - `src/Resources/scripts/admin/imageUpload.js` has been renamed to `src/Resources/scripts/admin/fileUpload.js` and all form of word `image` has been changed to `file`
        - `src/Resources/styles/admin/component/list/images.less` has been renamed to `src/Resources/styles/admin/component/list/files.less` and all form of word `image` has been changed to `file`
    - following methods has changed their interface:
        - `UploadedFileEntityConfig::__construct()`
            ```diff
             - public function __construct($entityName, $entityClass)
             + public function __construct(string $entityName, string $entityClass, array $types)
            ```
        - `UploadedFile::__construct()`
            ```diff
             - public function __construct($entityName, $entityId, $temporaryFilename)
             + public function __construct(string $entityName, int $entityId, string $type, string $temporaryFilename, int $position)
            ```
        - `UploadedFileFactory::create()` and `UploadedFileFactoryInterface::create`
            ```diff
             - public function create(string $entityName, int $entityId, array $temporaryFilenames)
             + public function create(string $entityName, int $entityId, string $type, string $temporaryFilename, int $position = 0)
            ```
    - update your project configuration files accordingly:
        - `config/packages/twig.yaml`
            ```diff
                - '@ShopsysFramework/Admin/Form/colorpickerFields.html.twig'
            +   - '@ShopsysFramework/Admin/Form/abstractFileuploadFields.html.twig'
                - '@ShopsysFramework/Admin/Form/fileuploadFields.html.twig'
                - '@ShopsysFramework/Admin/Form/imageuploadFields.html.twig'
            ```
        - `config/uploaded_files.yml`
            ```diff
            +   # It is best practice to name first type as "default"
            +   #
            +   # Example:
            +   # -   name: mailTemplate
            +   #     class: Shopsys\FrameworkBundle\Model\Mail\MailTemplate
            +   #     types:
            +   #         -   name: default
            +   #             multiple: false
            +
                -   name: mailTemplate
                    class: Shopsys\FrameworkBundle\Model\Mail\MailTemplate
            +       types:
            +           -   name: default
            +               multiple: true
            ```
- contact form has been moved to separate page. You can find the whole new setting in administration (`/admin/contact-form/`),
  where you can edit main text for contact form. ([#1522](https://github.com/shopsys/shopsys/pull/1522))

  There are few steps need to be done, to make contact form work on FE
    - in `ContactFormController` change previous `indexAction` with [the new one](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Controller/Front/ContactFormController.php). Please pay attention if you have some modification in previous implementation.
    - in `ContactFormController` add `ContactFormSettingsFacade` as dependency in constructor
    - in `ContactFormController` remove action `sendAction()`, it is not needed anymore
    - remove `src/Resources/scripts/frontend/contactForm.js`, it is not needed anymore
    - new localized route `front_contact` has been introduced with slug `contact`. This slug can be already in use in your project, because
      previous SSFW versions have an article called `Contact` which is used as the contact page. Please move the article's content to the new contact page and completely remove the article.
      To remove the article, please create [new migration](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Migrations/Version20191121171000.php) in you project which removes the article and its slug.

      If you don't want to remove the article, you will need to change path for the new route in the next step

    - add new localized route in all your localized routing `yml` files with translated `path` option
        ```diff
        +    front_contact:
        +       path: /contact/
        +       defaults: { _controller: App\Controller\Front\ContactFormController:indexAction }
        ```
    - add new template [`templates/Front/Content/ContactForm/index.html.twig`](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/ContactForm/index.html.twig)
    - add link to new contact page somewhere in templates (e.g in `footer.html.twig`)
        ```diff
            <div class="footer__bottom__articles">
               {{ getShopInfoPhoneNumber() }}
               {{ getShopInfoEmail() }}
               {{ render(controller('App\\Controller\\Front\\ArticleController:footerAction')) }}
        +      <a class="menu__item__link" href="{{ url('front_contact') }}">{{ 'Contact'|trans }}</a>
            </div>
        ```

- vats can be created and managed per domains ([#1498](https://github.com/shopsys/shopsys/pull/1498))
    - please read [upgrade instruction for vats per domain](https://github.com/shopsys/shopsys/blob/9.0/upgrade/upgrade-instruction-for-vats-per-domain.md)

- apply these changes to add support for naming uploaded files([#1547](https://github.com/shopsys/shopsys/pull/1547))
    - update your `composer.json`
        ```diff
            "ext-curl": "*",
        +   "ext-fileinfo": "*",
            "ext-gd": "*",
        ```
    - update your `docker/php-fpm/Dockerfile`
        ```diff
            RUN docker-php-ext-install \
                bcmath \
        +       fileinfo \
                gd \
        ```
    - add this route to the end of your `config/routes/shopsys_front.yml`
        ```diff
        +   front_download_uploaded_file:
        +       path: /file/{uploadedFileId}/{uploadedFilename}
        +       defaults: { _controller: App\Controller\Front\UploadedFileController:downloadAction }
        +       methods: [GET]
        +       requirements:
        +           uploadedFileId: \d+
        ```
    - update your `src/Controller/Front/OrderController.php`
        ```diff
             return new DownloadFileResponse(
                 $this->legalConditionsFacade->getTermsAndConditionsDownloadFilename(),
        -        $response->getContent()
        +        $response->getContent(),
        +        'text/html'
             );
        ```
    - update your `tests/App/Smoke/Http/RouteConfigCustomization.php`
        ```diff
                $config->addExtraRequestDataSet('Check personal data XML export with right hash')
                    ->setParameter('hash', $personalDataAccessRequest->getHash())
                    ->setExpectedStatusCode(200);
        +   })->customizeByRouteName(['front_download_uploaded_file'], function (RouteConfig $config) {
        +       $config->skipRoute('Downloading uploaded files is not tested.');
            });
        ```
    - add [src/Controller/Front/UploadedFileController.php](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Controller/Front/UploadedFileController.php) to your project
    - `MessageData::attachmentsFilepaths` has been replaced by `MessageData::attachments` that accepts array of `UploadedFile`
    - `MailTemplateFacade::getMailTemplateAttachmentsFilepaths()` has been replaced by `MailTemplateFacade::getMailTemplateAttachmentFilepath()` that accepts single `UploadedFile`
    - following methods has changed their interface, update your usages accordingly:
        - `UploadedFileLocator::__construct()`
            ```diff
             - public function __construct($uploadedFileDir, $uploadedFileUrlPrefix, FilesystemInterface $filesystem)
             + public function __construct($uploadedFileDir, FilesystemInterface $filesystem, DomainRouterFactory $domainRouterFactory)
            ```
        - `CustomerMailFacade::__construct()`
            ```diff
             - public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, RegistrationMail $registrationMail)
             + public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, RegistrationMail $registrationMail, UploadedFileFacade $uploadedFileFacade)
            ```
        - `OrderMailFacade::__construct()`
            ```diff
             - public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, OrderMail $orderMail)
             + public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, OrderMail $orderMail, UploadedFileFacade $uploadedFileFacade)
            ```
        - `PersonalDataAccessMailFacade::__construct()`
            ```diff
             - public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, PersonalDataExportMail $personalDataExportMail)
             + public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, PersonalDataExportMail $personalDataExportMail, UploadedFileFacade $uploadedFileFacade)
            ```
        - `ResetPasswordMailFacade::__construct()`
            ```diff
             - public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, ResetPasswordMail $resetPasswordMail)
             + public function __construct(Mailer $mailer, MailTemplateFacade $mailTemplateFacade, ResetPasswordMail $resetPasswordMail, UploadedFileFacade $uploadedFileFacade)
            ```
        - `MailTemplateDataFactory::__construct()`
            ```diff
             - public function __construct(UploadedFileFacade $uploadedFileFacade)
             + public function __construct(UploadedFileDataFactoryInterface $uploadedFileDataFactory)
            ```
        - `Mailer::__construct()`
            ```diff
             - public function __construct(Swift_Mailer $swiftMailer, Swift_Transport $realSwiftTransport)
             + public function __construct(Swift_Mailer $swiftMailer, Swift_Transport $realSwiftTransport, MailTemplateFacade $mailTemplateFacade)
            ```
        - `MessageData::__construct()`
            ```diff
             - public function __construct($toEmail, $bccEmail, $body, $subject, $fromEmail, $fromName, array $variablesReplacementsForBody = [], array $variablesReplacementsForSubject = [], array $attachments = [], $replyTo = null)
             + public function __construct($toEmail, $bccEmail, $body, $subject, $fromEmail, $fromName, array $variablesReplacementsForBody = [], array $variablesReplacementsForSubject = [], array $attachmentsFilepaths = [], $replyTo = null)
            ```
        - `UploadedFileFacade::__uploadFile()`
            ```diff
             - protected function uploadFile(object $entity, string $entityName, string $type, array $temporaryFilenames): void
             + protected function uploadFile(object $entity, string $entityName, string $type, string $temporaryFilename, string $uploadedFileName): void
            ```
        - `UploadedFileFacade::__uploadFiles()`
            ```diff
             - protected function uploadFiles(object $entity, string $entityName, string $type, array $temporaryFilenames, int $existingFilesCount): void
             + protected function uploadFiles(object $entity, string $entityName, string $type, array $temporaryFilenames, array $uploadedFileNames, int $existingFilesCount): void
            ```
        - `UploadedFileFactory::__create()` and `UploadedFileFactoryInterface::__create()`
            ```diff
             - public function create(string $entityName, int $entityId, string $type, string $temporaryFilename, int $position = 0): UploadedFile
             + public function create(string $entityName, int $entityId, string $type, string $temporaryFilename, string $uploadedFilename, int $position = 0): UploadedFile
            ```
        - `UploadedFileFactory::__createMultiple()` and `UploadedFileFactoryInterface::__createMultiple()`
            ```diff
             - public function createMultiple(string $entityName, int $entityId, string $type, array $temporaryFilenames, array $uploadedFilenames, int $existingFilesCount): array
             + public function createMultiple(string $entityName, int $entityId, string $type, array $temporaryFilenames, int $existingFilesCount): array
            ```
        - `UploadedFile::__construct()`
            ```diff
             - public function __construct(string $entityName, int $entityId, string $type, string $temporaryFilename, int $position)
             + public function __construct(string $entityName, int $entityId, string $type, string $temporaryFilename, string $uploadedFilename, int $position)
            ```

 - There is a new base html layout with horizontal menu and product filter placed in left panel, for detail information see [the separate article](upgrade-instructions-for-base-layout.md)
- update your project to use refactored customer structure ([#1543](https://github.com/shopsys/shopsys/pull/1543))
    - database table was changed from `users` to `customer_users`, change ORM mapping for entity `User`
        ```diff
           * @ORM\Table(
        -  *     name="users",
        +  *     name="customer_users",
           *     uniqueConstraints={
        ```
    - there were reorganized `User*` and `Customer*` classes and related methods
        - these classes were renamed and/or moved to different namespace, walk through all code occurrences and process changes
            - `App\Form\Admin\UserFormTypeExtension` to `App\Form\Admin\CustomerUserFormTypeExtension`
            - `Shopsys\FrameworkBundle\Form\Admin\Customer\CustomerFormType` to `Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserUpdateFormType`
            - `Shopsys\FrameworkBundle\Form\Admin\Customer\UserFormType` to `Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType`
            - `Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication\CustomerCommunicationFormType` to `Shopsys\FrameworkBundle\Form\Admin\CustomerCommunication\CustomerUserCommunicationFormType`
            - `Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer` to `Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerData` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactory` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerDataFactoryInterface` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactoryInterface`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifierFactory` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerListAdminFacade` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade`
            - `Shopsys\FrameworkBundle\Model\Customer\CustomerPasswordFacade` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserPasswordFacade`
            - `Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerException` to `Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserException`
            - `Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashException` to `Shopsys\FrameworkBundle\Model\Customer\Exception\InvalidResetPasswordHashUserException`
            - `Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerIdentifierException` to `Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerUserIdentifierException`
            - `Shopsys\FrameworkBundle\Model\Customer\Exception\UserNotFoundException` to `Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException`
            - `Shopsys\FrameworkBundle\Model\Customer\FrontendUserProvider` to `Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider`
            - `Shopsys\FrameworkBundle\Model\Customer\User` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser`
            - `Shopsys\FrameworkBundle\Model\Customer\UserData` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData`
            - `Shopsys\FrameworkBundle\Model\Customer\UserDataFactory` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory`
            - `Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFacade` to `Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFactory` to `Shopsys\FrameworkBundle\Model\Customer\UserFactory`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFactoryInterface` to `Shopsys\FrameworkBundle\Model\Customer\UserFactoryInterface`
            - `Shopsys\FrameworkBundle\Model\Customer\UserRepository` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository`
            - `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser` to `Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser`
            - `Tests\App\Functional\Model\Customer\CustomerFacadeTest` to `Tests\App\Functional\Model\Customer\CustomerUserFacadeTest`
        - these methods were moved and/or changed interface
            - `Shopsys\FrameworkBundle\Model\Cart\CartFacade::findCartOfCurrentCustomer()` to `Shopsys\FrameworkBundle\Model\Cart\CartFacade::findCartOfCurrentCustomerUser()`
            - `Shopsys\FrameworkBundle\Model\Cart\CartFacade::getCartOfCurrentCustomerCreateIfNotExists()` to `Shopsys\FrameworkBundle\Model\Cart\CartFacade::getCartOfCurrentCustomerUserCreateIfNotExists()`
            - `Shopsys\FrameworkBundle\Model\Cart\CartFacade::findCartByCustomerIdentifier()` to `Shopsys\FrameworkBundle\Model\Cart\CartFacade::findCartByCustomerUserIdentifier()`
            - `Shopsys\FrameworkBundle\Model\Cart\CartFacade::getCartByCustomerIdentifierCreateIfNotExists()` to `Shopsys\FrameworkBundle\Model\Cart\CartFacade::getCartByCustomerUserIdentifierCreateIfNotExists()`
            - `Shopsys\FrameworkBundle\Model\Customer\UserDataFactory::createFromUser()` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory::createFromCustomerUser()`
            - `Shopsys\FrameworkBundle\Model\Order\OrderFacade::getCustomerOrderList()` to `Shopsys\FrameworkBundle\Model\Order\OrderFacade::getCustomerUserOrderList()`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFacade::findUserByEmailAndDomain()` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::findCustomerUserByEmailAndDomain()`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFacade::getUserById()` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::getCustomerUserById()`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFacade::editByCustomer()` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::editByCustomerUser()`
            - `Shopsys\FrameworkBundle\Model\Customer\UserFacade::amendUserDataFromOrder()` to `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade::amendCustomerUserDataFromOrder()`
        - to keep your tests working you need tu update `UserDataFixture`
            - inject `CustomerFactoryInterface` via constructor into the class
            - create and assign an entity of `Customer` to `BillingAddressData` and `UserData`
                ```diff
                +   $customerUserData = $this->customerUserDataFactory->create();
                    $userData = $this->userDataFactory->createForDomainId($domainId);
                -   $billingAddressData = $this->billingAddressDataFactory->create();
                +   $userData->customer = $customerUserData->userData->customer;
                +   $billingAddressData = $customerUserData->billingAddressData;
                ```
        - as the `BillingAddress` is being connected with a customer, you are able to remove it from `User`
            ```diff
                public function __construct(
                    BaseUserData $userData,
            -       BillingAddress $billingAddress,
                    ?DeliveryAddress $deliveryAddress
                ) {
            ```
            - when you need `BillingAddress`, you can obtain it via `$userData->getCustomer()->getBillingAddress()`
            - also you need to affect all twig templates to respect a new structure (e.g.)
                ```diff
                -   {% set address = user.billingAddress %}
                +   {% set address = user.customer.billingAddress %}
                ```
        - method `Shopsys\FrameworkBundle\Model\Customer\UserFacade::createCustomerWithBillingAddress()` was extracted to new class `Shopsys\FrameworkBundle\Model\Customer\CustomerUserFacade`

- add hover timeout to horizontal menu ([#1564](https://github.com/shopsys/shopsys/pull/1564))
    - you can skip this task if you have your custom design
    - move loader to hidden submenu - so it can not interrupt hover `src/Resources/scripts/frontend/categoryPanel.js`
        ```diff
          function loadCategoryItemContent ($categoryItem, url) {
              Shopsys.ajax({
        -          loaderElement: $categoryItem,
        +          loaderElement: $categoryItem.find('.js-category-list-placeholder'),
        ```
    - add new js plugin hoverIntent v1.10.1 `src/Resources/scripts/frontend/plugins/jquery.hoverIntent.js` (https://github.com/shopsys/shopsys/tree/master/project-base/src/Resources/scripts/frontend/plugins/jquery.hoverIntent.js)
    - add new js component `src/Resources/scripts/frontend/components/hoverIntent.js` (https://github.com/shopsys/shopsys/tree/master/project-base/src/Resources/scripts/frontend/components/hoverIntent.js)
    - add class and data attributes to hover menu `templates/Front/Content/Category/panel.html.twig`
        ```diff
          {% for categoryWithLazyLoadedVisibleChildren in categoriesWithLazyLoadedVisibleChildren %}
              {% set isCurrentCategory = (currentCategory is not null and currentCategory == categoryWithLazyLoadedVisibleChildren.category) %}
        -     <li class="list-menu__item js-category-item">
        +     <li class="list-menu__item js-category-item js-hover-intent" data-hover-intent-force-click="true" data-hover-intent-force-click-element=".js-category-collapse-control">
                  <a href="{{ url('front_product_list'
        ```
- allow getting data for FE API from Elastic ([#1557](https://github.com/shopsys/shopsys/pull/1557))
    - add and change fields in your elasticsearch definition files
        ```diff
        -   main_variant
        +   is_main_variant

        +   "uuid": {
        +      "type": "text"
        +   },
        +   "unit": {
        +      "type": "text"
        +   },
        +   "is_using_stock": {
        +      "type": "boolean"
        +   },
        +   "stock_quantity": {
        +      "type": "boolean"
        +   },
        +   "variants": {
        +       "type": "integer"
        +   },
        +   "main_variant": {
        +      "type": "integer"
        +   }
        ```
        Be aware that to make this change in production environment you'll need to delete old structure and then create
        a new one because of the change of `type` in `main_variant` field. If you want to know more you can see [this article](../docs/introduction/console-commands-for-application-management-phing-targets.md#product-search-migrate-structure)

    - change and include new fields in ProductSearchExportWithFilterRepositoryTest
        ```diff
            'selling_denied',
        -   'main_variant',
        +   'is_main_variant',
            'visibility',
        +   'uuid',
        +   'unit',
        +   'is_using_stock',
        +   'stock_quantity',
        +   'variants',
        +   'main_variant',
        ```
    - if you extended these methods in `ProductOnCurrentDomainFacadeInterface`, `ProductOnCurrentDomainFacade` or `ProductOnCurrentDomainElasticFacade`,
     change their definitions (as strict types and typehints were added or changed)
        ```diff
        -   public function getVisibleProductById($productId);
        +   public function getVisibleProductById(int $productId): Product;

        //...

        -   public function getAccessoriesForProduct(Product $product);
        +   public function getAccessoriesForProduct(Product $product): array;

        //...

        -   public function getVariantsForProduct(Product $product);
        +   public function getVariantsForProduct(Product $product): array;

        //...

        -   public function getPaginatedProductsInCategory(ProductFilterData $productFilterData, $orderingModeId, $page, $limit, $categoryId);
        +   public function getPaginatedProductsInCategory(
        +       ProductFilterData $productFilterData,
        +       string $orderingModeId,
        +       int $page,
        +       int $limit,
        +       int $categoryId
        +   ): PaginationResult;

        //...

        -   public function getPaginatedProductsForBrand($orderingModeId, $page, $limit, $brandId);
        +   public function getPaginatedProductsForBrand(
        +       string $orderingModeId,
        +       int $page,
        +       int $limit,
        +       int $brandId
        +   ): PaginationResult;

        //...

        -   public function getPaginatedProductsForSearch($searchText, ProductFilterData $productFilterData, $orderingModeId, $page, $limit);
        +   public function getPaginatedProductsForSearch(
        +       string $searchText,
        +       ProductFilterData $productFilterData,
        +       string $orderingModeId,
        +       int $page,
        +       int $limit
        +   ): PaginationResult;

        //...

        -   public function getSearchAutocompleteProducts($searchText, $limit);
        +   public function getSearchAutocompleteProducts(?string $searchText, int $limit): PaginationResult;

        //...

        -   public function getProductFilterCountDataInCategory($categoryId, ProductFilterConfig $productFilterConfig, ProductFilterData $productFilterData);
        +   public function getProductFilterCountDataInCategory(
        +       int $categoryId,
        +       ProductFilterConfig $productFilterConfig,
        +       ProductFilterData $productFilterData
        +   ): ProductFilterCountData;

        //...

        -   public function getProductFilterCountDataForSearch($searchText, ProductFilterConfig $productFilterConfig, ProductFilterData $productFilterData);
        +   public function getProductFilterCountDataForSearch(
        +       ?string $searchText,
        +       ProductFilterConfig $productFilterConfig,
        +       ProductFilterData $productFilterData
        +   ): ProductFilterCountData;
        ```

- fix footer advert background and image position ([#1590](https://github.com/shopsys/shopsys/pull/1590))
    - if you have custom design you can skip this
    - add footer modification to `src/Resources/styles/front/common/components/in/place.less`
        ```diff
              .in-place {
                  margin-bottom: @in-place-margin;
                  text-align: center;
        +
        +         &--footer {
        +             padding: 10px 0;
        +             margin-bottom: 0;
        +         }
            }
        ```

    - remove class `.in-place.in-place--footer` in `src/Resources/styles/front/common/todo.less` as it's no longer necessary
        ```diff
        - .in-place.in-place--footer {
        -     padding-top: 10px;
        - }
        ```

    - change classes in `templates/Front/Layout/footer.html.twig`
        ```diff
        - <div class="web__line web__line--split dont-print">
        -     <div class="web__container footer__top">
        + <div class="web__line web__line--grey dont-print">
        +     <div class="web__container">
                  {{ render(controller('App\\Controller\\Front\\AdvertController:boxAction',{'positionName' : 'footer'})) }}
              </div>
          </div>
        ```

### Tools

- apply coding standards checks on your `app` folder ([#1306](https://github.com/shopsys/shopsys/pull/1306))
  - run `php phing standards-fix` and fix possible violations that need to be fixed manually

- if you want to add stylelint rules to check style coding standards [#1511](https://github.com/shopsys/shopsys/pull/1511)
    -  add new file [.stylelintignore](https://github.com/shopsys/shopsys/blob/9.0/project-base/.stylelintignore)
    -  add new file [.stylelintrc](https://github.com/shopsys/shopsys/blob/9.0/project-base/.stylelintrc)
    - update `gruntfile.js.twig` and add new task
        ```diff
        +   stylelint: {
        +      frontend: [
        +          '{{ customResourcesDirectory|raw }}/styles/**/*.less'
        +      ],
        +      admin: [
        +          '{{ frameworkResourcesDirectory|raw }}/styles/admin/**/*.less'
        +      ]
        +   },

            watch: {
                admin: {
        ```
        ```diff
            {% else -%}
        -       ['frontendLess{{ domain.id }}']
        +       ['frontendLess{{ domain.id }}','stylelint']
            {% endif -%}
        ```
        ```diff
            grunt.loadNpmTasks('grunt-spritesmith');
        +   grunt.loadNpmTasks('grunt-stylelint');

        -   grunt.registerTask('default', ["sprite:admin", "sprite:frontend", "webfont", "less", "postcss"]);
        +   grunt.registerTask('default', ["sprite:admin", "sprite:frontend", "webfont", "less", "postcss", "stylelint:frontend"]);
        ```
        ```diff
        -   grunt.registerTask('admin', ['sprite:admin', 'webfont:admin', 'less:admin', 'stylelint:admin']);
        +   grunt.registerTask('admin', ['sprite:admin', 'webfont:admin', 'less:admin']);
      ```
    - update `package.json`
        ```diff
            "grunt-spritesmith": "^6.6.2",
        +   "grunt-stylelint": "^0.12.0",
            "grunt-webfont": "^1.7.2",
            "jit-grunt": "^0.10.0",
        +   "stylelint": "^11.1.1",
            "time-grunt": "^1.4.0"
        ```
    - don't forget to rebuild your grunt file by command `php phing gruntfile` and update your npm dependencies by command `npm install`
    - to fix all your less files in command line by command `php phing stylelint-fix`
[shopsys/framework]: https://github.com/shopsys/framework
