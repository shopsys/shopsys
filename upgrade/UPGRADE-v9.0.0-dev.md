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

- remove left panel web layout and add horizontal menu ([#1540](https://github.com/shopsys/shopsys/pull/1540))
    - if you have your custom design you can skip this all task about twig files
    - because we don't have left panel on frontend anymore we have to center banner slider (or change its width to 100% - don't forget to change image size and reupload images from administration) [src/Resources/styles/front/common/components/box/slider.less](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Resources/styles/front/common/components/box/slider.less)
        ```diff
          @box-slider-width: @web-width - @web-panel-width - 2*@web-padding;
          @box-slider-point-size: 8px;
        + @box-slider-bottom-gap: 30px;

          .box-slider {
              display: none;

              @media @query-lg {
                  display: block;
                  position: relative;
                  margin-bottom: @margin-gap;
                  width: @box-slider-width;
                  max-width: 100%;
        +         margin: 0 auto @box-slider-bottom-gap auto;
                  visibility: hidden;
        ```
    - change left menu to horizontal menu [src/Resources/styles/front/common/components/list/menu.less](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/Resources/styles/front/common/components/list/menu.less)
        ```diff
          @list-menu-padding-right: 25px;
          @list-menu-arrow-size: 25px;
        + @list-menu-height: 30px;

          // mobile + tablet version
          @media @query-tablet {
              .list-menu {
        -         background: @list-menu-mobile-bg;
        +         background-color: @list-menu-mobile-bg;

          @media @query-lg {
              .list-menu {
                  .reset-ul();
        -         margin-bottom: @margin-gap;
        +         display: none;
        +         position: absolute;
        +         width: 100%;
        +         left: 0;
        +         top: @list-menu-height;
        +         z-index: 9;

        -         &__item {
        -             display: block;
        +         &--root {
        +             position: relative;
        +             display: flex;
        +             flex-direction: row;
        +             width: 100%;
        +             top: 0;
        +             margin-bottom: @margin-gap;

        -             border-top: 1px solid @color-border;
        -             background: @color-light;
        +             background-color: @color-light;
        +         }

        -             &:first-child {
        -                 border-top: 0;
        +         &--dropdown {
        +             position: relative;
        +             top: 0px;
        +         }

        +         &__item {
        +             background-color: @color-light;

        +             .list-menu--root & {
        +                 display: flex;
        +                 flex-direction: column;
        +                 align-items: flex-start;
        +             }

                      &__link {
        -                 display: block;

                      & & {
                          margin-bottom: 0;

                          border-top: 1px solid @color-border;
        +                 background-color: @color-light;
                      }
        ```

    - find and change layout in all twig files and wrap all content to `.web__line` and `.web__container` elements
        ```diff
        - {% extends 'Front/Layout/layoutWithPanel.html.twig' %}
        + {% extends 'Front/Layout/layoutWithoutPanel.html.twig' %}
        ```

        Because we removed panel, we have to wrap all content to `.web__line` and `.web__container` elements
        ```diff
        -    <div class="web__line">
        +        <div class="web__container">
        +            ... old content ...
        +        </div>
        +    </div>
        ```

        - check all these changes in these files:
            - [templates/Front/Content/Brand/list.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Brand/list.html.twig)
            - [templates/Front/Content/ContactForm/contactForm.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/ContactForm/contactForm.html.twig)
            - [templates/Front/Content/ContactForm/index.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/ContactForm/index.html.twig)
            - [templates/Front/Content/Product/detail.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Product/detail.html.twig)
            - [templates/Front/Content/Product/list.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Product/list.html.twig)
            - [templates/Front/Content/Product/listByBrand.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Product/listByBrand.html.twig)
            - [templates/Front/Content/Product/search.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Product/search.html.twig)

        - in these files we need to change only first line and change page layout:
            - [templates/Front/Content/Error/error.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Error/error.html.twig)
            - [templates/Front/Content/Default/index.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Default/index.html.twig)

        - move left panel under header [templates/Front/Layout/layout.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Layout/layout.html.twig)
            ```diff
              <div class="web__in">
                  <div class="web__header">
                      <div class="web__line">
                          <div class="web__container">
                              {{ block('header') }}
                          </div>
                      </div>
                  </div>
            +     <div class="web__line">
            +         <div class="web__container">
            +             {{ render(controller('App\\Controller\\Front\\CategoryController:panelAction', { request: app.request } )) }}
            +         </div>
            +     </div>
            ```

      - add slidedown behaviour to horizontal menu [templates/Front/Content/Category/panel.html.twig](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Category/panel.html.twig)
          ```diff
            {% if categoriesWithLazyLoadedVisibleChildren|length > 0 %}
          -     <ul class="js-category-list list-menu dont-print {% if isFirstLevel %}list-menu--root{% endif %}" {% if isFirstLevel %}id="js-categories"{% endif %}>

          +     <ul class="js-category-list list-menu dont-print {% if isFirstLevel %}list-menu--root{% endif %}
          +     {% if categoriesWithLazyLoadedVisibleChildren[0].category.level > 2 %}list-menu--dropdown{% endif %}
          +     " {% if isFirstLevel %}id="js-categories"{% endif %}>
          ```
          ```diff
            {{ categoryWithLazyLoadedVisibleChildren.category.name }}
            {% if categoryWithLazyLoadedVisibleChildren.hasChildren %}
          -    <i class="list-menu__item__control svg svg-arrow js-category-collapse-control {% if categoryWithLazyLoadedVisibleChildren.category in openCategories %}open{% endif %}" data-url="{{ url('front_category_branch', { parentCategoryId: categoryWithLazyLoadedVisibleChildren.category.id }) }}"></i>
          +    <i class="list-menu__item__control svg svg-arrow js-category-collapse-control" data-url="{{ url('front_category_branch', { parentCategoryId: categoryWithLazyLoadedVisibleChildren.category.id }) }}"></i>
            {% endif %}
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
         -      ['frontendLess{{ domain.id }}']
         +      ['frontendLess{{ domain.id }}','stylelint']
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

    - don't forget to rebuild your grunt file by command `php phing gruntifle` and update your npm dependencies by command `npm install`
  
    - to fix all your less files in command line by command `php phing stylelint-fix`
[shopsys/framework]: https://github.com/shopsys/framework
