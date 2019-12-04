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
        +       defaults: { _controller: ShopsysShopBundle:Front\ContactForm:index }
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
### Tools

- apply coding standards checks on your `app` folder ([#1306](https://github.com/shopsys/shopsys/pull/1306))
  - run `php phing standards-fix` and fix possible violations that need to be fixed manually

[shopsys/framework]: https://github.com/shopsys/framework
