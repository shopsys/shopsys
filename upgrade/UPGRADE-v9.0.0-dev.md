# [Upgrade from v8.1.0-dev to v9.0.0-dev](https://github.com/shopsys/shopsys/compare/v8.1.0...HEAD)

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

- update your `docker/php-fpm/Dockerfile` ([#1605](https://github.com/shopsys/shopsys/pull/1605))
    - move switching user to `www-data` above creating `.npm-global` folder
        ```diff
        +   # Switch to user
        +   USER www-data

            RUN mkdir /home/www-data/.npm-global
            ENV NPM_CONFIG_PREFIX /home/www-data/.npm-global

        -   # Switch to user
        -   USER www-data
        ```
    - remove lock for NPM version
        ```diff
        -   # hotfix for https://github.com/npm/cli/issues/613
        -   RUN npm install -g npm@6.13.2
        ```

- upgrade to PostgreSQL 12 ([#1601](https://github.com/shopsys/shopsys/pull/1601))
    - update `docker/php-fpm/Dockerfile`
    
        ```diff
            # install PostgreSQl client for dumping database
            RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
                sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt/ $(lsb_release -sc)-pgdg main" > /etc/apt/sources.list.d/PostgreSQL.list' && \
        -       apt-get update && apt-get install -y postgresql-10 postgresql-client-10 && apt-get clean
        +       apt-get update && apt-get install -y postgresql-12 postgresql-client-12 && apt-get clean
        ```
    
    - update `tests/App/Test/Codeception/Module/Db.php`

        ```diff
            public function _afterSuite()
            {
        -       $this->cleanup();
                $this->_loadDump();
            }
        
            public function cleanup()
            {
                /** @var \Tests\App\Test\Codeception\Helper\SymfonyHelper $symfonyHelper */
                $symfonyHelper = $this->getModule(SymfonyHelper::class);
                /** @var \Shopsys\FrameworkBundle\Component\Doctrine\DatabaseSchemaFacade $databaseSchemaFacade */
                $databaseSchemaFacade = $symfonyHelper->grabServiceFromContainer(DatabaseSchemaFacade::class);
                $databaseSchemaFacade->dropSchemaIfExists('public');
        -       $databaseSchemaFacade->createSchema('public');
        +   }
        +
        +   /**
        +    * @inheritDoc
        +    */
        +   public function _loadDump($databaseKey = null, $databaseConfig = null)
        +   {
        +       $this->cleanup();
        +       return parent::_loadDump($databaseKey, $databaseConfig);
            }
        ```

    - upgrading server running in Kubernetes
        - when upgrading production environment you should turn maintenance on `kubectl exec [pod-name-php] -c [container] -- php phing maintenance-on`
        - dump current database by running `kubectl exec [pod-name-postgres] -- bash -c "pg_dump -U [postgres-user] [database-name]" > database.sql` (in case you are using more databases repeat this step for each database)
        - change service version in `kubernetes/deployments/postgres.yml`

            ```diff
                containers:
                    -   name: postgres
            -           image: postgres:10.5-alpine
            +           image: postgres:12.1-alpine
            ``` 
    
        - apply new configuration `kubectl apply -k kubernetes/kustomize/overlays/<overlay>`
        - import dumped data into new database server by running `cat database.sql | kubectl exec -i [pod-name-postgres] -- psql -U [postgres-user] -d [database-name]` (this needs to be done for each database dumped from first step)
        - turn maintenance off `kubectl exec [pod-name-php] -c [container] -- php phing maintenance-off`

    - upgrading server running in Docker
        - dump current database by running `docker-compose exec postgres pg_dumpall -l <database_name> -f /var/lib/postgresql/data/<database_name>.backup` (in case you are using more databases repeat this step for each database)
        - backup current database mounted volume `mv var/postgres-data/pgdata var/postgres-data/pgdata.old`
        - change service version in `docker-compose.yml`
            - you should change it also in all `docker-compose*.dist` files such as:
                - `docker/conf/docker-compose.yml.dist`
                - `docker/conf/docker-compose-mac.yml.dist`
                - `docker/conf/docker-compose-win.yml.dist`

            ```diff
                services:
                    postgres:
            -           image: postgres:10.5-alpine
            +           image: postgres:12.1-alpine
            ``` 

        - rebuild and create containers with `docker-compose up -d --build`
        - import dumped data into new database server by running `docker-compose exec postgres psql -f /var/lib/postgresql/data/<database_name>.backup <database_name>` (this needs to be done for each database dumped from first step)
        - if everything works well you may remove backuped data `rm -r var/postgres-data/pgdata.old`
    - for native installation we recommend to upgrade to version 11 first and then to version 12
        - to prevent unexpected behavior do not try this in production environment before previous testing
        - you should follow official instructions with using [pg_upgrade](https://www.postgresql.org/docs/12/pgupgrade.html) or [pg_dumpall](https://www.postgresql.org/docs/12/app-pg-dumpall.html)
            - [migration to version 11](https://www.postgresql.org/docs/11/release-11.html#id-1.11.6.11.4)
            - [migration to version 12](https://www.postgresql.org/docs/12/release-12.html#id-1.11.6.6.4)
        - do not forget to check for BC breaks which may be introduced for your project

- upgrade to Elasticsearch 7 ([#1602](https://github.com/shopsys/shopsys/pull/1602))
    - first of all we recommend to take a look at [Breaking changes](https://www.elastic.co/guide/en/elasticsearch/reference/7.5/release-notes-7.0.0.html) section in Elasticsearch documentation to prevent failures

    - update `docker/elasticsearch/Dockerfile`

        ```diff
        -   FROM docker.elastic.co/elasticsearch/elasticsearch-oss:6.3.2
        +   FROM docker.elastic.co/elasticsearch/elasticsearch-oss:7.6.0
        ```

    - remove `_doc` node from mapping in `src/Recources/definition` json files

        ```diff
            "mappings": {
        -       "_doc": {
        -           "properties": {
        -               "name": {
        -                   "type": "text"
        -               }
        -           }
        -       }
        +       "properties": {
        +           "name": {
        +               "type": "text"
        +           }
        +       }
            }
        ```

    - add kibana to your `docker-compose.yml`

        ```diff
        +   kibana:
        +       image: docker.elastic.co/kibana/kibana-oss:7.6.0
        +       container_name: shopsys-framework-kibana
        +       depends_on:
        +           - elasticsearch
        +       ports:
        +           - "5601:5601"
        ```

        - you should add it also in all `docker-compose*.dist` files such as:
            - `docker/conf/docker-compose.yml.dist`
            - `docker/conf/docker-compose-mac.yml.dist`
            - `docker/conf/docker-compose-win.yml.dist`

    - migrate elasticsearch indexes by `php phing elasticsearch-index-migrate`

    - upgrading server running in Kubernetes
        - apply new configuration `kubectl apply -k kubernetes/kustomize/overlays/<overlay>`
        - run elasticsearch migration `kubectl exec -i [pod-name-php-fpm] -- ./phing elasticsearch-index-migrate`
    
    - upgrading server running in Docker
        - rebuild and create containers with `docker-compose up -d --build`
        - run elasticsearch migration `docker-compose exec php-fpm ./phing elasticsearch-index-migrate`
    
    - upgrading native installation we recommend to follow Elasticsearch [documentation](https://www.elastic.co/guide/en/cloud/current/ec-upgrading-v7.html)  

- upgrade the Adminer Docker image to 4.7.6 ([#1717](https://github.com/shopsys/shopsys/pull/1717))
    - change the Docker image of Adminer from `adminer:4.7` to `adminer:4.7.6` in your `docker-compose.yml` config, `docker-compose*.yml.dist` templates and `kubernetes/deployments/adminer.yml`:

        ```diff
        - image: adminer:4.7
        + image: adminer:4.7.6
        ```

    - run `docker-compose up -d` so the new image is pulled and used

### Configuration
- add trailing slash to all your localized paths for `front_product_search` route ([#1067](https://github.com/shopsys/shopsys/pull/1067))
    - be aware, if you already have such paths (`hledani/`, `search/`) in your application
    - the change might cause problems with your SEO as well

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
- add [`app/getEnvironment.php`](https://github.com/shopsys/shopsys/blob/master/project-base/app/getEnvironment.php) file to your project ([#1368](https://github.com/shopsys/shopsys/pull/1368))
- add optional [Frontend API](https://github.com/shopsys/shopsys/blob/master/docs/frontend-api/introduction-to-frontend-api.md) to your project ([#1445](https://github.com/shopsys/shopsys/pull/1445), [#1486](https://github.com/shopsys/shopsys/pull/1486), [#1493](https://github.com/shopsys/shopsys/pull/1493), [#1489](https://github.com/shopsys/shopsys/pull/1489)):
    - add `shopsys/frontend-api` dependency with `composer require shopsys/frontend-api`
    - register necessary bundles in `config/bundles.php`
        ```diff
            Shopsys\FormTypesBundle\ShopsysFormTypesBundle::class => ['all' => true],
        +   Shopsys\FrontendApiBundle\ShopsysFrontendApiBundle::class => ['all' => true],
        +   Overblog\GraphQLBundle\OverblogGraphQLBundle::class => ['all' => true],
        +   Overblog\GraphiQLBundle\OverblogGraphiQLBundle::class => ['dev' => true],
            Shopsys\GoogleCloudBundle\ShopsysGoogleCloudBundle::class => ['all' => true],
        ```
    - add new route file [`config/routes/frontend-api.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/routes/frontend-api.yml) from GitHub
    - add new route file [`config/routes/dev/frontend-api-graphiql.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/routes/dev/frontend-api-graphiql.yaml) from GitHub
    - copy [type definitions from Github](https://github.com/shopsys/shopsys/tree/master/project-base/config/graphql/types) into `config/graphql/types/` folder
    - copy necessary configuration [shopsys_frontend_api.yml from Github](https://github.com/shopsys/shopsys/blob/master/project-base/config/packages/shopsys_frontend_api.yml) to `config/packages/shopsys_frontend_api.yml`
    - copy [tests for FrontendApiBundle from Github](https://github.com/shopsys/shopsys/tree/master/project-base/tests/FrontendApiBundle) to your `tests` folder
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
- update your project to use refactored FileUpload functionality with added support for multiple files ([#1531](https://github.com/shopsys/shopsys/pull/1531/))
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
        - `FileUploadType` now requires options `entity`, `file_entity_class` and `file_type` see [documentation](https://docs.shopsys.com/en/latest/introduction/using-form-types/#fileuploadtype) for more info
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
    - in `ContactFormController` change previous `indexAction` with [the new one](https://github.com/shopsys/shopsys/blob/master/project-base/src/Controller/Front/ContactFormController.php). Please pay attention if you have some modification in previous implementation.
    - in `ContactFormController` add `ContactFormSettingsFacade` as dependency in constructor
    - in `ContactFormController` remove action `sendAction()`, it is not needed anymore
    - remove `src/Resources/scripts/frontend/contactForm.js`, it is not needed anymore
    - new localized route `front_contact` has been introduced with slug `contact`. This slug can be already in use in your project, because
      previous SSFW versions have an article called `Contact` which is used as the contact page. Please move the article's content to the new contact page and completely remove the article.
      To remove the article, please create [new migration](https://github.com/shopsys/shopsys/blob/master/project-base/src/Migrations/Version20191121171000.php) in you project which removes the article and its slug.

      If you don't want to remove the article, you will need to change path for the new route in the next step

    - add new localized route in all your localized routing `yml` files with translated `path` option
        ```diff
        +    front_contact:
        +       path: /contact/
        +       defaults: { _controller: App\Controller\Front\ContactFormController:indexAction }
        ```
    - add new template [`templates/Front/Content/ContactForm/index.html.twig`](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Content/ContactForm/index.html.twig)
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
    - please read [upgrade instruction for vats per domain](https://github.com/shopsys/shopsys/blob/master/upgrade/upgrade-instruction-for-vats-per-domain.md)

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
    - add [src/Controller/Front/UploadedFileController.php](https://github.com/shopsys/shopsys/blob/master/project-base/src/Controller/Front/UploadedFileController.php) to your project
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
        +   "main_variant_id": {
        +      "type": "integer"
        +   }
        ```
        Be aware that to make this change in production environment you'll need to migrate old structure.
        If you want to know more you can see [this article](../docs/introduction/console-commands-for-application-management-phing-targets.md#elasticsearch-index-migrate)

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
        +   'main_variant_id',
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
- add cart detail on hover ([#1565](https://github.com/shopsys/shopsys/pull/1565))
  
  - you can skip this task if you have your custom design
  - Add new file [`src/Resources/styles/front/common/layout/header/cart-detail.less`](https://github.com/shopsys/shopsys/blob/master/project-base/src/Resources/styles/front/common/layout/header/cart-detail.less)
  - Update your `src/Resources/styles/front/common/layout/header/cart.less` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-bc98fd209f1c026440cbf870086beece)
  - Update your `src/Resources/styles/front/common/main.less`
      ```diff
        @import "layout/header/cart.less";
      + @import "layout/header/cart-detail.less";
        @import "layout/header/cart-mobile.less";
      ```
  - Add new file [`templates/Front/Inline/Cart/cartBoxItemMacro.html.twig`](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Inline/Cart/cartBoxItemMacro.html.twig)
  - Update your `templates/Front/Inline/Cart/cartBox.html.twig` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-41605908c87d6192f16bdf03da67b192)
  - Update your `templates/Front/Layout/header.html.twig` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-fec16681aa60ba908bc8e574d24de3fd)
  - Add new file [`assets/js/frontend/cart/cartBoxItemRemover.js`](https://github.com/shopsys/shopsys/blob/master/project-base/assets/js/frontend/cart/cartBoxItemRemover.js)
  - Update `assets/js/frontend/cart/cartBox.js`
      ```diff
        Ajax.ajax({
            loaderElement: '#js-cart-box',
            url: $(event.currentTarget).data('reload-url'),
      +     data: { 'isIntentActive': $(event.currentTarget).hasClass('active'), loadItems: true },
            type: 'get',
            success: function (data) {
                $('#js-cart-box').replaceWith(data);
                ...
            }
        });
      ```
  
  - Update `assets/js/frontend/cart/index.js`
      ```diff
        import './cartRecalculator';
      + import './CartBoxItemRemover';
      ```
  
  - Update your `src/Controller/Front/CartController.php` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-2cc95b0ea7402f2767d208da32b41333)
  
  - Update your `config/routes/shopsys_front.yml`
      ```diff
      + front_cart_delete_ajax:
      +     path: /cart/delete-ajax/{cartItemId}/
      +     defaults:
      +         _controller: App\Controller\Front\CartController:deleteAjaxAction
      +     requirements:
      +         cartItemId: \d+
      +     condition: "request.isXmlHttpRequest()"
      + front_cart_box_detail:
      +     path: /cart/box-detail
      +     defaults:
      +         _controller: App\Controller\Front\CartController:boxDetailAction
      ```

  - Update your `assets/js/frontend/components/hoverIntent.js` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-0c8ac3a092aa65b5548bba44aaf47934)

  - Update your `tests/App/Acceptance/acceptance/CartCest.php` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-1cdd5de922474f9286fd26767312abe6) 
  - Update your `tests/App/Acceptance/acceptance/PageObject/Front/CartPage.php` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-22d067f5c4b216b5f2809f6d6340bfee)
  - Update your `tests/App/Acceptance/acceptance/OrderCest.php` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-d697251fab7d514841306ad608a65fc5)
  - Update your `tests/App/Acceptance/acceptance/PageObject/Front/OrderPage.php` like in the [diff](https://github.com/shopsys/shopsys/pull/1565/files#diff-d2e52049c05d13eea5291229d1a2e6da)

- set loaderElement of searchAutocomplete component to search button (removed from body) [#1626](https://github.com/shopsys/shopsys/pull/1626)
    - update your `assets/js/frontend/components/searchAutocomplete.js`
        ```diff
          Ajax.ajaxPendingCall('Shopsys.search.autocomplete.searchRequest', {
        -     loaderElement: null,
        +     loaderElement: '.js-search-autocomplete-submit',
            // ...
        });
        ```
    - update your `templates/Front/Content/Search/searchBox.html.twig`
        ```diff
        -  <button type="submit" class="btn search__form__button">
        +  <button type="submit" class="btn search__form__button js-search-autocomplete-submit">
               {{ 'Search [verb]'|trans }}
           </button>
        ```
- fix domain icon rendering and loading ([#1655](https://github.com/shopsys/shopsys/pull/1655))
    - remove trailing slash in `config/paths.yml`
        ```diff
        -   shopsys.domain_images_url_prefix: '/%shopsys.content_dir_name%/admin/images/domain/'
        +   shopsys.domain_images_url_prefix: '/%shopsys.content_dir_name%/admin/images/domain'
        ```
    - fix icon path in `ImageDataFixture` (`src/DataFixtures/Demo/ImageDataFixture.php`)
        ```diff
            public function load(ObjectManager $manager)
            {
                $this->truncateImagesFromDb();
                if (file_exists($this->dataFixturesImagesDirectory)) {
        -           $this->moveFilesFromLocalFilesystemToFilesystem($this->dataFixturesImagesDirectory . 'domain/', $this->targetDomainImagesDirectory);
        +           $this->moveFilesFromLocalFilesystemToFilesystem($this->dataFixturesImagesDirectory . 'domain/', $this->targetDomainImagesDirectory . '/');
        ```
- check your custom form types with currencies after Money input ([#1675](https://github.com/shopsys/shopsys/pull/1675))
    - form field option `currency` is now rendered with `appendix_block` block (inside span tag) instead of plain text

- update your project to use refactored Elasticsearch related classes ([#1622](https://github.com/shopsys/shopsys/pull/1622))
    - update `config/services/cron.yml` if you have registered products export by yourself

        ```diff
        -   Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportCronModule:
        +   Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportCronModule:
        ```

    - update `config/services_test.yml`
    
        ```diff
        -   Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository: ~
        +   Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository: ~
        ```

    - remove `\Tests\App\Functional\Component\Elasticsearch\ElasticsearchStructureUpdateCheckerTest`
    - update `ProductSearchExportWithFilterRepositoryTest`
        - move the class from `\Tests\App\Functional\Model\Product\Search\ProductSearchExportWithFilterRepositoryTest` to `Tests\App\Functional\Model\Product\Elasticsearch\ProductExportRepositoryTest`
        - update annotation for property `$repository`

            ```diff
                /**
            -    * @var \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository
            +    * @var \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository
                 * @inject
                 */
                private $repository;
            ```

        - remove unused argument of method `getExpectedStructureForRepository()` and all its usages

            ```diff
                /**
            -    * @param \Shopsys\FrameworkBundle\Model\Product\Search\Export\ProductSearchExportWithFilterRepository $productSearchExportRepository
                 * @return string[]
                 */
            -   private function getExpectedStructureForRepository(ProductSearchExportWithFilterRepository $productSearchExportRepository): array
            +   private function getExpectedStructureForRepository(): array
            ```

    - update `FilterQueryTest`
        - define `use` statement for `ProductIndex`

            ```diff
                use Shopsys\FrameworkBundle\Component\Money\Money;
            +   use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
                use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
            ```

        - inject `IndexDefinitionLoader` instead of removed `ElasticsearchStructureManager`

            ```diff
                /**
            -    * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\ElasticsearchStructureManager
            +    * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
                 * @inject
                 */
            -   private $elasticSearchStructureManager;
            +   private $indexDefinitionLoader;
            ```

        - update `createFilter()` method

            ```diff
                protected function createFilter(): FilterQuery
                {
            -       $elasticSearchIndexName = $this->elasticSearchStructureManager->getAliasName(Domain::FIRST_DOMAIN_ID, self::ELASTICSEARCH_INDEX);
            -       $filter = $this->filterQueryFactory->create($elasticSearchIndexName);
            +       $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), Domain::FIRST_DOMAIN_ID);
            +       $filter = $this->filterQueryFactory->create($indexDefinition->getIndexAlias());

                    return $filter->filterOnlySellable();
                }
            ```
- update your project to export to Elasticsearch only changed products ([#1636](https://github.com/shopsys/shopsys/pull/1636))
    - if you have registered products export by yourself, you can update `config/services/cron.yml` to run frequently export of only changed products and full export at midnight
    ```diff
        Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportCronModule:
            tags:
    -           - { name: shopsys.cron, hours: '*', minutes: '*' }
    +           - { name: shopsys.cron, hours: '0', minutes: '0' }

    +   Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportChangedCronModule:
    +       tags:
    +           - { name: shopsys.cron, hours: '*', minutes: '*' }
    ```

- update FpJsFormValidator bundle ([#1664](https://github.com/shopsys/shopsys/pull/1664))
    - update your `composer.json`
      ```diff
            "require": {
      -         "fp/jsformvalidator-bundle": "^1.5.1",
      +         "fp/jsformvalidator-bundle": "1.5.x-dev",
            }
      ```
    - update your `.eslintignore`
      ```diff
        /assets/js/commands/translations/mocks
      + /assets/js/bundles
      ```
    - update your `.gitignore`
      ```diff
        /assets/js/translations.json
      + /assets/js/bundles
      ```
- update your application to support multiple delivery addresses ([#1635](https://github.com/shopsys/shopsys/pull/1635))
    - some methods has changed so you might want to update their usage in your application:
        - `Customer::getDeliveryAddress()` and `Customer::setDeliveryAddress()` has been removed you can use `Customer::getDeliveryAddresses()` or `CustomerUser::getDefaultDeliveryAddress()` instead
        - `CustomerUserFacade::editDeliveryAddress()` has been removed, use `DeliveryAddressFacade::edit()` instead
        - `CustomerUser::__construct()`
            ```diff
            -   public function __construct(CustomerUserData $customerUserData, ?DeliveryAddress $deliveryAddress)
            +   public function __construct(CustomerUserData $customerUserData)
            ```
        - `CustomerUserFacade::__construct()`
            ```diff
                public function __construct(
                    EntityManagerInterface $em,
                    CustomerUserRepository $customerUserRepository,
                    CustomerUserUpdateDataFactoryInterface $customerUserUpdateDataFactory,
                    CustomerMailFacade $customerMailFacade,
                    BillingAddressFactoryInterface $billingAddressFactory,
                    DeliveryAddressFactoryInterface $deliveryAddressFactory,
                    BillingAddressDataFactoryInterface $billingAddressDataFactory,
                    CustomerUserFactoryInterface $customerUserFactory,
                    CustomerUserPasswordFacade $customerUserPasswordFacade,
            -       CustomerFacade $customerFacade
            +       CustomerFacade $customerFacade,
            +       DeliveryAddressFacade $deliveryAddressFacade
                ) {
            ```
        - `CustomerUserFacade::createCustomerUser()`
            ```diff
                protected function createCustomerUser(
                    Customer $customer,
            -       CustomerUserData $customerUserData,
            -       ?DeliveryAddressData $deliveryAddressData = null
            +       CustomerUserData $customerUserData
                ): CustomerUser
            ```
        - `CustomerUserFacade::amendCustomerUserDataFromOrder()` 
            ```diff
              -   public function amendCustomerUserDataFromOrder(CustomerUser $customerUser, Order $order)
              +   public function amendCustomerUserDataFromOrder(CustomerUser $customerUser, Order $order, ?DeliveryAddress $deliveryAddress) 
            ```
        - `CustomerUserFacade::edit()`
            ```diff
              -   protected function edit(int $customerUserId, CustomerUserUpdateData $customerUserUpdateData)
              +   protected function edit(int $customerUserId, CustomerUserUpdateData $customerUserUpdateData, ?DeliveryAddress $deliveryAddress = null)
            ```
        - `CustomerUserFactory::create() and CustomerUserFactoryInterface::create()`
            ```diff
            -    public function create(CustomerUserData $customerUserData, ?DeliveryAddress $deliveryAddress): CustomerUser
            +    public function create(CustomerUserData $customerUserData): CustomerUser
            ```
        - `CustomerUserUpdateDataFactory::createAmendedByOrder()` and `CustomerUserUpdateDataFactoryInterface::createAmendedByOrder()`
            ```diff
              -   public function createAmendedByOrder(CustomerUser $customerUser, Order $order): CustomerUserUpdateData
              +   public function createAmendedByOrder(CustomerUser $customerUser, Order $order, ?DeliveryAddress $deliveryAddress): CustomerUserUpdateData
            ```
        - `OrderFacade::createOrderFromFront()`
            ```diff
              -   public function createOrderFromFront(OrderData $orderData)
              +   public function createOrderFromFront(OrderData $orderData, ?DeliveryAddress $deliveryAddress)
            ```
    - there has been changes in project files, that you should apply in your project:
        - update your `assets/js/frontend.js` file
            ```diff
                // HP entry?
                import './frontend/homepage/slickInit';
            +   
            +   import './frontend/deliveryAddress';
            ```
        - add [assets/js/frontend/deliveryAddress/deliveryAddress.js](https://github.com/shopsys/shopsys/tree/master/project-base/assets/js/frontend/deliveryAddress/deliveryAddress.js) and [assets/js/frontend/deliveryAddress/index.js]((https://github.com/shopsys/shopsys/tree/master/project-base/assets/js/frontend/deliveryAddress/deliveryAddress.js)) files
        - update your `config/packages/twig.yaml`
            ```diff
                - '@ShopsysFramework/Admin/Form/productCalculatedPrices.html.twig'
            +   - '@ShopsysFramework/Front/Form/deliveryAddressChoiceFields.html.twig'
            +   - '@ShopsysFramework/Admin/Form/deliveryAddressListFields.html.twig'
            ```
        - update your `assets/js/frontend/validation/form/order.js`
            ```diff
            +   const selectedDeliveryAddressValue = $orderPersonalInfoForm.find('.js-delivery-address-input:checked').val();
                const groups = [constant('\\Shopsys\\FrameworkBundle\\Form\\ValidationGroup::VALIDATION_GROUP_DEFAULT')];
            -   if ($orderPersonalInfoForm.find('#order_personal_info_form_deliveryAddressFilled').is(':checked')) {
            +   if ($orderPersonalInfoForm.find('#order_personal_info_form_deliveryAddressFilled').is(':checked') && (selectedDeliveryAddressValue === '' || selectedDeliveryAddressValue === undefined)) {
                    groups.push(constant('\\App\\Form\\Front\\Customer\\DeliveryAddressFormType::VALIDATION_GROUP_DIFFERENT_DELIVERY_ADDRESS'));
            ```
        - update your `config/routes/shopsys_front.yml` - add to end of file
            ```diff
            +   front_customer_delivery_address_delete:
            +       path: /customer/delete-delivery-address/{deliveryAddressId}
            +       defaults:
            +           _controller: App\Controller\Front\CustomerController:deleteDeliveryAddressAction
            +           deliveryAddressId: 0
            +       methods: [GET]
            +       requirements:
            +           deliveryAddressId: \d+
            ```
        - update these files from [pull request diff](https://github.com/shopsys/shopsys/pull/1635/files)
            - `src/Controller/Front/CustomerController.php`
            - `src/Controller/Front/OrderController.php`
            - `src/Form/Front/Customer/DeliveryAddressFormType.php`
            - `src/Form/Front/Customer/User/CustomerUserFormType.php`
            - `src/Form/Front/Order/PersonalInfoFormType.php`
            - `src/Model/Customer/User/CustomerUser.php`
            - `templates/Front/Content/Customer/edit.html.twig`
            - `templates/Front/Content/Order/step3.html.twig`
            - `templates/Front/Content/PersonalData/adress.xml.twig`
            - `templates/Front/Content/PersonalData/detail.html.twig`
            - `templates/Front/Content/PersonalData/export.xml.twig`
            - `templates/Front/Content/PersonalData/order.html.twig`
            - `templates/Front/Form/theme.html.twig`
            - `tests/App/Functional/PersonalData/PersonalDataExportXmlTest.php`
            - `tests/App/Unit/Form/Front/Order/PersonalInfoFormTypeTest.php`

- fix functional tests for single domain usage ([#1682](https://github.com/shopsys/shopsys/pull/1682))
    - if you do not plan use your project configured with single domain you may skip this
    - add method following method into `tests/App/Functional/Model/Order/OrderTransportAndPaymentTest.php`, `tests/App/Functional/Model/Payment/IndependentPaymentVisibilityCalculationTest.php`, `tests/App/Functional/Model/Transport/IndependentTransportVisibilityCalculationTest.php`

        ```php
        /**
         * @param bool[] $enabledForDomains
         * @return bool[]
         */
        private function getFilteredEnabledForDomains(array $enabledForDomains): array
        {
            return array_intersect_key($enabledForDomains, array_flip($this->domain->getAllIds()));
        }
        ```

        - find in those classes assignments into `TransportData::enabled` and `PaymentData::enabled` and filter the array by added method `getFilteredEnabledForDomains()` - like in examples bellow

        ```diff
        -   $transportData->enabled = $enabledForDomains;
        +   $transportData->enabled = $this->getFilteredEnabledForDomains($enabledForDomains);
        ```

        ```diff
        -   $paymentData->enabled = [
        +   $paymentData->enabled = $this->getFilteredEnabledForDomains([
                self::FIRST_DOMAIN_ID => true,
                self::SECOND_DOMAIN_ID => false,
        -   ];
        +   ]);
        ```

    - skip test when only one domain is configured with adding code bellow at the beginning of test in
        - `Tests\App\Functional\Model\Payment\PaymentDomainTest::testCreatePaymentWithDifferentVisibilityOnDomains()`
        - `Tests\App\Functional\Model\Transport\TransportDomainTest::testCreateTransportWithDifferentVisibilityOnDomains()`
        
            ```php
            if (count($this->domain->getAllIds()) === 1) {
                $this->markTestSkipped('Test is skipped for single domain');
            }
            ```
- update your code to have easier extension of customer related classes ([#1700](https://github.com/shopsys/shopsys/pull/1700/))
    - `Customer::addBillingAddress()` and `Customer::addDeliveryAddress()` are no longer public, use `CustomerFacade::create()` and `CustomerFacade::edit()` methods instead
    - `CustomerFacade::createCustomerWithBillingAddress()` no longer exists, use `CustomerFacade::create()` and `BillingAddressFacade::create()` instead
    - some methods have changed their interface, update your code usages:
        - `Customer::__construct()`
            ```diff
            -   public function __construct()
            +   public function __construct(CustomerData $customerData)
            ```
        - `CustomerFacade::__construct()`
            ```diff
            -   public function __construct(EntityManagerInterface $em, CustomerFactoryInterface $customerFactory, BillingAddressFactoryInterface $billingAddressFactory)
            +   public function __construct(EntityManagerInterface $em, CustomerFactoryInterface $customerFactory, CustomerRepository $customerRepository)
            ```
        - `CustomerFactory::create()` and `CustomerFactoryInterface::create()`
            ```diff
            -   public function create(): Customer
            +   public function create(CustomerData $customerData): Customer
            ```
        - `CustomerUserFacade::__construct()`
            ```diff
                CustomerFacade $customerFacade,
            -   DeliveryAddressFacade $deliveryAddressFacade
            +   DeliveryAddressFacade $deliveryAddressFacade,
            +   CustomerDataFactoryInterface $customerDataFactory,
            +   BillingAddressFacade $billingAddressFacade
            ```
    - `tests/App/Functional/PersonalData/PersonalDataExportXmlTest.php` has been changed, see [diff of PR](https://github.com/shopsys/shopsys/pull/1700/files) to update it

- update your application to refresh administrator roles after edit own profile ([#1514](https://github.com/shopsys/shopsys/pull/1514))
    - some methods has changed so you might want to update their usage in your application:
        - `AdministratorController::__construct()`
            ```diff
                public function __construct(
                    AdministratorFacade $administratorFacade,
                    GridFactory $gridFactory, GridFactory $gridFactory,
                    BreadcrumbOverrider $breadcrumbOverrider,
                    AdministratorActivityFacade $administratorActivityFacade,
            -       AdministratorDataFactoryInterface $administratorDataFactory
            +       AdministratorDataFactoryInterface $administratorDataFactory,
            +       AdministratorRolesChangedFacade $administratorRolesChangedFacade
                 )
            ```
        - `AdministratorController::editAction()`
            ```diff
            -   public function editAction(Request $request, $id)
            +   public function editAction(Request $request, int $id)
            ```
        - `SAdministratorRolesChangedSubscriber::__construct()`
            ```diff
            -    public function __construct(TokenStorageInterface $tokenStorage, AdministratorFacade $administratorFacade)
            +    public function __construct(TokenStorageInterface $tokenStorage, AdministratorRolesChangedFacade $administratorRolesChangedFacade)
            ```

- add cron overview ([#1407](https://github.com/shopsys/shopsys/pull/1407))
    - update your files using [this diff](https://github.com/shopsys/project-base/commit/fdac77abc9fd7f167ccd544f4691ee25b2de169d)

- update your aplication to do not change product availability to default when availability can not be calculated immediately ([#1659](https://github.com/shopsys/shopsys/pull/1659))
    - see #project-base-diff to update your project
    
- update your application to have czech crowns on czech domain and euro on english domain ([#1542](https://github.com/shopsys/shopsys/pull/1542))
    - see #project-base-diff to update your project
    

- update your application to symfony4 ([#1704](https://github.com/shopsys/shopsys/pull/1704))
    
    - see #project-base-diff to update your project
    
    - minimum memory requirements for installation using Docker on MacOS and Windows has changed, please read  [Installation Using Docker for MacOS](docs/installation/installation-using-docker-macos.md) or [Installation Using Docker for Windows 10 Pro and higher](docs/installation/installation-using-docker-windows-10-pro-higher.md)
    
    - some methods has changed so you might want to update their usage in your application:
    
        - `RouterDebugCommandForDomain::__construct()`
            ```diff
            -   public function __construct(DomainChoiceHandler $domainChoiceHelper, $router = null)
            +   public function __construct(DomainChoiceHandler $domainChoiceHelper, RouterInterface $router, ?FileLinkFormatter $fileLinkFormatter = null)
            ```
        - `RouterDebugCommandForDomain::__execute()`
            ```diff
            -   protected function execute(InputInterface $input, OutputInterface $output)
            +   protected function execute(InputInterface $input, OutputInterface $output): int
            ```
        - `RouterMatchCommandForDomain::__construct()`
            ```diff
            -   public function __construct(DomainChoiceHandler $domainChoiceHelper, $router = null)
            +   public function __construct(DomainChoiceHandler $domainChoiceHelper, RouterInterface $router)
            ```
        - `RouterMatchCommandForDomain::execute()`
            ```diff
            -   protected function execute(InputInterface $input, OutputInterface $output)
            +   protected function execute(InputInterface $input, OutputInterface $output): int
            ```
        - `ConfirmDeleteResponseFactory::__construct()`
            ```diff          
                public function __construct(
            -       EngineInterface $templating,
            +       Environment $twigEnvironment,
                    RouteCsrfProtector $routeCsrfProtector
                )
            ```
        - `FilesystemLoader::__construct`
            ```diff
                public function __construct(
            -       FileLocatorInterface $locator,
            -       TemplateNameParserInterface $parser,
                    ?string $rootPath = null,
                    ?Domain $domain = null
                ) {
            ```
        - `ErrorExtractor::getAllErrorsAsArray()`
            ```diff           
            -   public function getAllErrorsAsArray(Form $form, Bag $flashMessageBag)
            +   public function getAllErrorsAsArray(Form $form, FlashBag $flashMessageBag)
            ```
        - `Grid::__construct()`
            ```diff           
                public function __construct(
                    $id,
                    DataSourceInterface $dataSource,
                    RequestStack $requestStack,
                    RouterInterface $router,
                    RouteCsrfProtector $routeCsrfProtector,
            -       Twig_Environment $twig
            +       Environment $twig
                )
            ```
        - `GridFactory::__construct()`
            ```diff
                public function __construct(
                    RequestStack $requestStack,
                    RouterInterface $router,
                    RouteCsrfProtector $routeCsrfProtector,
            -       Twig_Environment $twig
            +       Environment $twig
                )
            ```
        - `GridView::__construct()`
            ```diff
                public function __construct(
                    Grid $grid,
                    RequestStack $requestStack,
                    RouterInterface $router,
            -       Twig_Environment $twig,
            +       Environment $twig,
                    $theme,
                    array $templateParameters = []
                )
            ```
        - `CustomTransFiltersVisitor::doEnterNode()`
            ```diff
            -   protected function doEnterNode(Twig_Node $node, Twig_Environment $env)
            +   protected function doEnterNode(Node $node, Environment $env)
            ```
        - `CustomTransFiltersVisitor::replaceCustomFilterName()`
            ```diff
            -   protected function replaceCustomFilterName(Twig_Node_Expression_Filter $filterExpressionNode, $newFilterName)
            +   protected function replaceCustomFilterName(FilterExpression $filterExpressionNode, $newFilterName)
            ```
        - `CustomTransFiltersVisitor::doLeaveNode()`
            ```
            -   protected function doLeaveNode(Twig_Node $node, Twig_Environment $env)
            +   protected function doLeaveNode(Node $node, Environment $env)
            ```
        - `CartWatcherFacade::__construct()`
            ```diff
                public function __construct(
            -       FlashMessageSender $flashMessageSender,
            +       FlashBagInterface $flashBag,
                    EntityManagerInterface $em,
                    CartWatcher $cartWatcher,
            -       CurrentCustomerUser $currentCustomerUser
            +       CurrentCustomerUser $currentCustomerUser,
            +       Environment $twigEnvironment
                ) {
            ```
        - `ContactFormFacade::__construct()`
            ```diff             
                public function __construct(
                    MailSettingFacade $mailSettingFacade,
                    Domain $domain,
                    Mailer $mailer,
            -       Twig_Environment $twig
            +       Environment $twig
                ) {
            ```
        - `FeedRenderer::__construct()`
            ```diff
            -   public function __construct(Twig_Environment $twig, Twig_TemplateWrapper $template)
            +   public function __construct(Environment $twig, TemplateWrapper $template)
            ```
        - `FeedRendererFactory::__construct()`
            ```diff
            -   public function __construct(Twig_Environment $twig)
            +   public function __construct(Environment $twig)
            ```
        - `OrderMail::__construct()`
            ```diff
                public function __construct(
                    Setting $setting,
                    DomainRouterFactory $domainRouterFactory,
            -       Twig_Environment $twig,
            +       Environment $twig,
                    OrderItemPriceCalculation $orderItemPriceCalculation,
                    Domain $domain,
                    PriceExtension $priceExtension,
            ```
        - `Authenticator::__construct()`
            ```diff
                public function __construct(
            -       TokenStorage $tokenStorage,
            -       TraceableEventDispatcher $traceableEventDispatcher
            +       TokenStorageInterface $tokenStorage,
            +       EventDispatcherInterface $eventDispatcher
              ) {
            ```
        - `ImageExtension::__construct()`
            ```diff
                public function __construct(
                    $frontDesignImageUrlPrefix,
                    Domain $domain,
                    ImageLocator $imageLocator,
                    ImageFacade $imageFacade,
            -       EngineInterface $templating,
            +       Environment $twigEnvironment,
                    bool $isLazyLoadEnabled = false
                ) {
            ```
        - `MailerSettingExtension::__construct()`
            ```diff         
            -   public function __construct(ContainerInterface $container, EngineInterface $templating)
            +   public function __construct(ContainerInterface $container, Environment $twigEnvironment)
           ```
    
    - some methods was removed
        - `AdminBaseController.php::getFlashMessageSender` (you can use `FlashMessageTrait`)
        
    - these classes were removed so you might need to update your application appropriately:
        - `Bag`(you can use `FlashMessageTrait`)
        - `BagNameIsNotValidException`
        - `FlashMessageException`
        - `FlashMessageSender` (you cn use `FlashMessageTrait`)
        - `CannotConvertToJsonException`
        - `ConstantNotFoundException`
        - `JsConstantCompilerException`
        - `JsConstantCompilerPass`
        - `JsCompiler`
        - `JsCompilerPassInterface`
        - `JsTranslatorCompilerPass`
        - `JsConstantCallParserException`
        - `JsConstantCall`
        - `JsConstantCallParser`
        - `JsParserException`
        - `UnsupportedNodeException`
        - `JsFunctionCallParser`
        - `JsStringParser`
        - `JsTranslatorCallParserException`
        - `JsTranslatorCall`
        - `JsTranslatorCallParser`
        - `JsTranslatorCallParserFactory`
        - `JavascriptCompiler`
        - `NotLogFakeHttpExceptionsExceptionListener` (you can use `NotLogFakeHttpExceptionsErrorListener`)
      
    - these constants were removed so you might need to update your application appropriately:
        - `Roles::ROLE_ADMIN_AS_CUSTOMER`
- update your application to include login in your frontend API ([#1731](https://github.com/shopsys/shopsys/pull/1731))
    - see #project-base-diff to update your project

- update your application to include refresh tokens in your frontend API ([#1736](https://github.com/shopsys/shopsys/pull/1736))
    - see #project-base-diff to update your project
    
- update your application to signed tokens by private key in your frontend API ([#1742](https://github.com/shopsys/shopsys/pull/1742))
    - see #project-base-diff to update your project

- fix your version of jms/translation-bundle to 1.4.4 in order to prevent problems with translations dumping ([#1732](https://github.com/shopsys/shopsys/pull/1732))
    - see #project-base-diff to update your project

- fix your password minimum length constraint message ([#1478](https://github.com/shopsys/shopsys/pull/1478))
    - see #project-base-diff to update your project

- fix your translations ids ([#1738](https://github.com/shopsys/shopsys/pull/1738))
    - see #project-base-diff to update your project
    - email template variable has been changed from `{e-mail}` to `{email}`, update your email templates accordingly
    - run `php phing translations-dump` and check, if some translations are needed to be translated

- method name in `HeurekaCategoryDownloader` has been changed ([#1740](https://github.com/shopsys/shopsys/pull/1740))
    - `HeurekaCategoryDownloader::convertToShopEntities()` has been renamed to `HeurekaCategoryDownloader::convertToHeurekaCategoriesData()` update your project appropriately

- move cron definitions in your project so it is easier to control them ([#1739](https://github.com/shopsys/shopsys/pull/1739))
    - see #project-base-diff to update your project
    
- compliance with the principle of encapsulation in the project base ([#1640](https://github.com/shopsys/shopsys/pull/1640))
    - see #project-base-diff to update your project

### Tools

- apply coding standards checks on your `app` folder ([#1306](https://github.com/shopsys/shopsys/pull/1306))
  - run `php phing standards-fix` and fix possible violations that need to be fixed manually

- if you want to add stylelint rules to check style coding standards [#1511](https://github.com/shopsys/shopsys/pull/1511)
    -  add new file [.stylelintignore](https://github.com/shopsys/shopsys/blob/master/project-base/.stylelintignore)
    -  add new file [.stylelintrc](https://github.com/shopsys/shopsys/blob/master/project-base/.stylelintrc)
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

### Frontend

- javascript assets are managed by webpack and npm ([#1545](https://github.com/shopsys/shopsys/pull/1545), [#1645](https://github.com/shopsys/shopsys/pull/1645))
    - please read [upgrade instruction for webpack](./upgrade-instruction-for-webpack.md)

If you have custom frontend you can skip these tasks:
- hide variant table header when product is denied for sale ([#1634](https://github.com/shopsys/shopsys/pull/1634))
    - add new condition at product detail file: `templates/Front/Content/Product/detail.html.twig`
        ```diff
        -   {% if product.isMainVariant %}
        +   {% if product.isMainVariant and not product.calculatedSellingDenied %}
                <table {% getProductSellingPrice(product) is not null %}itemprop="offers"
        ```

[shopsys/framework]: https://github.com/shopsys/framework
