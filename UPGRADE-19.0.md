# UPGRADING FROM 18.x to 19.0

The releases of Shopsys Platform adhere to the [Backward Compatibility Promise](https://docs.shopsys.com/en/latest/contributing/backward-compatibility-promise/) to make the upgrades to new versions easier and help long-term maintainability.

## Recommended way of upgrading

- upgrade only your composer dependencies and follow the instructions in the guide below
- upgrade locally first - after you fix all issues caused by the upgrade, commit your changes, test your application, and then continue with a deployment onto your server
- upgrade one version at a time:
    - start with a working application
    - upgrade to the next version
    - fix all the issues you encounter
    - repeat
- check the instructions in all sections; any of them could be relevant to you
- the typical upgrade sequence should be:
    - run `docker compose down --volumes` to turn off your containers
    - _(macOS only)_ run `mutagen project terminate` first, then `docker compose down --volumes`
    - follow upgrade notes in the _Infrastructure_ section (related to `docker-compose.yml`, `Dockerfile`, docker containers, `nginx.conf`, `php.ini`, etc.)
    - _(MacOS, Windows only)_ run `docker-sync start` to create volumes
    - run `docker compose build --no-cache --pull` to build your images without cache and with the latest version
    - run `docker compose up -d --force-recreate --remove-orphans` to start the application again
    - update the `shopsys/*` dependencies in `composer.json` to the version you are upgrading to
        - e.g., `"shopsys/framework": "v7.0.0"`
    - follow upgrade notes in the _Composer dependencies_ section (related with `composer.json`)
    - run `composer update shopsys/* --with-dependencies`
    - run `npm install` to update the NPM dependencies
    - follow all upgrade notes you have not done yet
    - run `php phing clean`
    - run `php phing db-migrations` to run the database migrations
    - test your app locally
    - commit your changes
    - run `composer update` to update the rest of your dependencies, test the app again, and commit `composer.lock`
- if any of the database migrations do not suit you, there is an option to skip it; see [our Database Migrations docs](https://docs.shopsys.com/en/latest/introduction/database-migrations/#reordering-and-skipping-migrations)
- we may miss something even if we care a lot about these instructions. In case something doesn't work after the upgrade, you'll find more information in the [CHANGELOG](CHANGELOG.md)

#### Movement of features from project-base to packages

- in this version, there are quite a lot of features that have been moved from `project-base` to the packages, mostly to the `framework` and the `frontend-api` package
- each section in the upgrade guide contains a link to the `project-base` diff and besides the particular upgrade instructions, there is also a list of the moved features you should be aware of (if there are any)
- if your project was originally not developed from the Commerce Cloud version, or it was developed on a version lower than `v13.0.0`, these feature movements should not affect you during the upgrade
- otherwise, you might need to adjust your project to the changes:
- if you had no custom changes in the moved features, you should be fine, you can safely remove the features from your project and use the ones from the packages (project-base diff in each section will help you with that)
- if you had custom changes in the moved features, you will need to adjust your project to the changes
- you should remove everything that was not modified in your project and keep just the custom changes using the recommended ways of the [framework extensibility](https://docs.shopsys.com/en/latest/extensibility/)
- one way or another, you should pay a special attention to the database migrations that were added with the feature movement. If they suit your needs, you should keep them and remove the original migrations from your project, otherwise, you should skip the newly added migrations.

#### Introduction of strict types

- with each change, we are updating most classes that have been altered by that change to use strict types
- this means that you will need to update your project to use strict types as well
- we do not see writing upgrade notes for such changes as beneficial, as it would mean for you to check every single change manually even if only a few occurrences would apply to your project
- we are currently not aware of easy way to automate this process, so you will need to do it manually
- probably the easiest way is to run `composer install`, `php phing standards-fix` and `php phing phpstan` commands, which will fail on errors caused by incompatibility strict types and fix those errors manually

#### Add referer URL validation for social login to prevent open redirect ([#4320](https://github.com/shopsys/shopsys/pull/4320))

- `\Shopsys\FrontendApiBundle\Controller\SocialNetworkController` constants renamed:
    - `REFERER_URL` has been renamed to `SESSION_REFERER_URL`
    - `CART_UUID` has been renamed to `SESSION_CART_UUID`
    - `PRODUCT_LIST_UUIDS` has been renamed to `SESSION_PRODUCT_LIST_UUIDS`
    - `SHOULD_OVERWRITE_CART` has been renamed to `SESSION_SHOULD_OVERWRITE_CART`

#### make createInstance() method responsible only for its intended purpose ([#4338](https://github.com/shopsys/shopsys/pull/4338))

- the `createInstance()` method in factories is now responsible only for creating an instance of the given class, any default value setting or other logic has been moved to appropriate methods
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/81ba39b3704b70bca8c33baa749ea5664133ac24) to update your project

#### add New Year's Day to data fixtures ([#4342](https://github.com/shopsys/shopsys/pull/4342))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/50c6cd1ef5a9b31b5a55aaf64ba7b5e03fd26ad9) to update your project

#### Parameter of type colour can have Image instead of RGB hex ([#4325](https://github.com/shopsys/shopsys/pull/4325))

- `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData::$colourIcon` property has been renamed to `colorIcon` to standardize on American English spelling
- `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue::$colourIcon` property has been removed completely as it was unused
- `Shopsys\FrontendApiBundle\Component\Files\FileApiFacade` was removed, use `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade` instead
- `Shopsys\FrontendApiBundle\Component\Files\FileApiRepository` was removed, use `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileRepository` instead
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - GraphQL filter option type definitions moved from `project-base/app/config/graphql/types/ModelType/Product/Filter/` to `packages/frontend-api/src/Resources/config/graphql-types/ModelType/Product/Filter/` as decorators
    - affected types: `BrandFilterOption`, `FlagFilterOption`, `ParameterFilterOption` (interfaces and implementations), `ProductFilterOptions`
    - project-base files now inherit from decorators instead of containing full definitions
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5a69a806782fba6c1d5bbb855df7936b09596d28) to update your project

#### improve ProductRepositoryTest ([#4346](https://github.com/shopsys/shopsys/pull/4346))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b6df0e2f8beccf3d5a5bffca3338f6454886eb2a) to update your project

#### Email template image component ([#4353](https://github.com/shopsys/shopsys/pull/4353))

- added possibility to resize image in email template
- updated image component load
- allowed h1 tag in mail template
- fixed grapesjs mail button
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/819c77b6d529247fb16830f0cfaf8f0f0abd07f3) to update your project

#### upgrade symfony/doctrine-messenger ([#4365](https://github.com/shopsys/shopsys/pull/4365))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - migration for creating `messenger_messages` table (see `Shopsys\FrameworkBundle\Migrations\Version20241223020557`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/86f3f32eb5abf72a8b276edd0ba6bcd446a2f3d2) to update your project

#### few simple tweaks and fixes ([#4364](https://github.com/shopsys/shopsys/pull/4364))

- see #project-base-diff and #project-base-diff-4383 to update your project

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/861bc7ca0abb08c875224dee8ad47557407e9cda) to update your project

#### ProductTest tweaks ([#4370](https://github.com/shopsys/shopsys/pull/4370))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d5256229dd072b54f80488d345a30b940768d322) to update your project

#### enable GoPay notifications on http auth secured sites ([#4372](https://github.com/shopsys/shopsys/pull/4372))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a1df3124f689aafb6c687139cf9fb450a6a3eb1b) to update your project

#### Catalog page ([#4378](https://github.com/shopsys/shopsys/pull/4378))

- if your project already has an SEO page named `Catalog` (or its localized equivalent) or a friendly URL with slug with same naming, **review migration `Version20250119100000` before running it** — the migration silently reuses or skips those existing records, which may leave the catalog page unreachable or incorrectly linked
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fd20ed5907ed73e892e82ab1a99fca9e8661dd74) to update your project

#### reverted movement of vendor and node_modules syncing from Mutagen to named volumes ([#4381](https://github.com/shopsys/shopsys/pull/4381))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/20a31cb59781f453ab88ffd43ded18bcbaa7b79a) to update your project

#### Fix keys for React lists & add missing api unique identifiers ([#4368](https://github.com/shopsys/shopsys/pull/4368))

- `Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade::create()` now returns `Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar` entity
    - if you have extended this class and overridden the `create()` method, update your return type accordingly
- new `uuid` field added to `Shopsys\FrameworkBundle\Model\Complaint\ComplaintItem` and `Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar` entities
    - GraphQL types `ComplaintItem` and `NotificationBar` now include `uuid` field
    - if you use these types in your custom GraphQL queries, you can now access the `uuid` field

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a685c0cdb4c78c74e9d3c926b67c4e98da6779d1) to update your project

#### SF product detail - add parameter value color preview ([#4382](https://github.com/shopsys/shopsys/pull/4382))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the framework package:
    - `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository::extractParametersIncludedVariants()` method and related parameter extraction logic
    - `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getProductParameterValuesDataByProducts()` method
    - `Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter::fillEmptyParameters()` extension
    - `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository::extractParametersIncludedVariants()` method
    - `Product::$relatedProducts` property with all the related functionality
- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory::createParametersArrayFromProductArray()` and `mapParameterArray()`extensions
    - `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper::getParameters()` method now handles variants and color icons
    - GraphQL `Parameter#group` field definition moved to `ParameterDecorator.types.yaml`
    - GraphQL `Product#relatedProducts` field definition moved to `ProductDecorator.types.yaml` along with its mapper methods
- method `createMultipleForProduct()` in `Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory` was removed - use `createParametersArrayFromProductArray()` with product data array instead
- method `extractParameters()` in `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportRepository` was replaced with `extractParametersIncludedVariants()`
- Elasticsearch product index fields `parameter_is_dimensional` and `ordering_priority` (inside parameters) were removed
- `Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository::getProductsByParameterValues()` was removed
    - it was replaced by `Shopsys\FrameworkBundle\Model\Product\AffectedProductsRepository::getProductIdsWithParameterValues()` that returns just an array of product ids instead of entities
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2446629c05091ce1ade74a8744653383b04b48ee) to update your project

#### files picker tweaks ([#4373](https://github.com/shopsys/shopsys/pull/4373))

- `Shopsys\FrameworkBundle\Form\MultiLocaleFileUploadType` has been removed
    - use `Shopsys\FrameworkBundle\Form\FileUploadType` instead, which now automatically supports multilocale names based on entity type config (`require_friendly_name` setting)
- `Shopsys\FrameworkBundle\Form\MultiLocaleBasicFileUploadType` has been removed
    - use `Shopsys\FrameworkBundle\Form\BasicFileUploadType` with `'with_names_inputs' => true` option instead
- `Shopsys\FrameworkBundle\Form\BasicFileUploadType` options `allow_filenames_input` and `allow_localized_names` have been removed
    - use new `with_names_inputs` option instead (enables both filename and localized name inputs when set to `true`)
- `Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileFacade::uploadFilesWithoutRelations()` visibility changed from `public` to `protected`
    - the feature is now wrapped into new public `create()` method that also creates the relations from `UploadedFileFormData` input
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c062e60ffafe8dae3c36cbef73025d18d07e2bdc) to update your project

#### move a DB migration from project-base to the framework package ([#4388](https://github.com/shopsys/shopsys/pull/4388))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a92d4c62b3120d4c0d11109d88f09971d92f600f) to update your project

#### Seo category for limited user ([#4389](https://github.com/shopsys/shopsys/pull/4389))

- a limited user can no longer see SEO categories that contain a price filter
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b69eeeb6be5c668e462f70fe9af8ca38bfec6bdd) to update your project

#### upgrade postgreSQL version to 18.x ([#4149](https://github.com/shopsys/shopsys/pull/4149))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6222e7ccb658010b36ade265a4ac9904379f97f9) to update your project, see also [project-base diff](https://www.github.com/shopsys/project-base/commit/6222e7ccb658010b36ade265a4ac9904379f97f9)-4450 for additional changes

#### fix gitlab failing cypress tests ([#4391](https://github.com/shopsys/shopsys/pull/4391))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1db64cd7a4dba28ce63d5701bfa282e89da91760) to update your project

#### Migration from Doctrine annotations to PHP 8 attributes ([#4395](https://github.com/shopsys/shopsys/pull/4395))

- migrate annotations to attributes using Rector
    - follow [Rector upgrade guide](https://docs.shopsys.com/en/18.0/project/upgrade-your-project-with-rector/) with the following setlists:
        - `DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES`
        - `DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES`
        - `SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES`
    - fix Rector edge cases
        - fix double backslashes (`\\App` → `\App`)
        - ensure `targetEntity` uses `::class` syntax (`targetEntity: Category::class`)
- check Entity mappings are correct
    ```bash
    php bin/console doctrine:mapping:info
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/454668fac1dcfc4d9b0df0cde9c00de65750f68c) to update your project

#### send all order status change mail templates in demo data ([#4397](https://github.com/shopsys/shopsys/pull/4397))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/99ee365f889cb6f498bae5afba57909b1bf69aeb) to update your project

#### fixed php-fpm configuration ([#4401](https://github.com/shopsys/shopsys/pull/4401))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a66402bf4c3f51cd9ce6d41c0e505c522558da11) to update your project

#### removed symfony/proxy-manager-bridge dependency ([#4402](https://github.com/shopsys/shopsys/pull/4402))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/09d8c30ea1187212815505e10d23ce5b7168970e) to update your project

#### replace mutagen ignore relative paths with absolute paths ([#4403](https://github.com/shopsys/shopsys/pull/4403))

- relative ignore paths in `mutagen.yml.dist` were converted to absolute paths to prevent ignoring unintended directories (e.g., `/docs/storefront` was being ignored due to relative `/storefront` pattern)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6a85d5a8c13d5fbb3a45f6123e8a1f7a0b5f8ada) to update your project

#### improve product demo data + minor cleanup ([#4408](https://github.com/shopsys/shopsys/pull/4408))

- class `Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator` was removed as dead code
    - if you were using this class, implement your own path comparison logic
- class `Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess` was removed as dead code
    - if you were using this class, implement your own file access control logic
- class `Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException` was removed as dead code
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/98acd81c0673bc6513a88c23ec70a6d27b0ee491) to update your project

#### Updated constraint classes and usages for Symfony 7 compatibility ([#4409](https://github.com/shopsys/shopsys/pull/4409))

- if you have extended or created custom constraint classes, update them to use constructor-promoted properties instead of public properties with `getRequiredOptions()` like this:

    ```diff
    class Contains extends Constraint
    {
    -    public string $message = 'Field must contain {{ needle }}.';
    -
    -    public ?string $needle = null;
    -
    -    /**
    -     * {@inheritdoc}
    -     */
    -    #[Override]
    -    public function getRequiredOptions(): array
    -    {
    -        return [
    -            'needle',
    -        ];
    -    }
    +    /**
    +     * @param array<string, mixed>|null $options
    +     * @param string $needle
    +     * @param string $message
    +     * @param array|null $groups
    +     * @param mixed $payload
    +     */
    +    #[HasNamedArguments]
    +    public function __construct(
    +        ?array $options = null,
    +        public string $needle,
    +        public string $message = 'Field must contain {{ needle }}.',
    +        ?array $groups = null,
    +        mixed $payload = null,
    +    ) {
    +        if (is_array($options)) {
    +            DeprecationHelper::trigger(
    +                'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.',
    +                static::class,
    +            );
    +        }
    +
    +        parent::__construct($options, $groups, $payload);
    +    }
    +
    +    /**
    +     * {@inheritdoc}
    +     */
    +    #[Override]
    +    public function getTargets(): string|array
    +    {
    +        return self::PROPERTY_CONSTRAINT;
    +    }
    }
    ```

    - follow [Rector upgrade guide](https://docs.shopsys.com/en/18.0/project/upgrade-your-project-with-rector/) with the following setlists:
        - `ConstraintOptionsToNamedArgumentsRector::class`

- update all constraint instantiations to use named parameters instead of associative arrays like this:
    ```diff
    - new UniqueEntityField([
    -     'entityInstance' => $adminToEdit,
    -     'message' => 'Administrator with user name "{{ value }}" is already registered',
    -     'fieldName' => 'username',
    -     'entityName' => Administrator::class,
    - ])
    + new UniqueEntityField(
    +     entityInstance: $adminToEdit,
    +     message: 'Administrator with user name "{{ value }}" is already registered',
    +     fieldName: 'username',
    +     entityName: Administrator::class,
    + )
    ```
- replace deprecated `$request->get()` with specific property bag accessors

    | Old Usage                                     | New Usage                              |
    | --------------------------------------------- | -------------------------------------- |
    | `$request->get('param')` for query string     | `$request->query->get('param')`        |
    | `$request->get('param')` for POST data        | `$request->request->get('param')`      |
    | `$request->get('param')` for route attributes | `$request->attributes->get('param')`   |
    | `(int)$request->get('param')`                 | `$request->query->getInt('param')`     |
    | `(bool)$request->get('param')`                | `$request->query->getBoolean('param')` |
    | `$request->get('param') !== null`             | `$request->query->has('param')`        |
    | `$request->get('arrayParam')` for arrays      | `$request->request->all('arrayParam')` |

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/99ee19fb4db29eb244925e5087de2530e572658f) to update your project

#### enabled new coding standards to enforce strict types ([#4396](https://github.com/shopsys/shopsys/pull/4396))

##### The following steps should be performed immediately during the upgrade

- update your `ecs.php` and `ecs-skip-rules.php` configurations based on the changes in project-base
    - see https://github.com/shopsys/project-base/blob/v19.0.0/app/ecs.php
    - see https://github.com/shopsys/project-base/blob/v19.0.0/app/ecs-skip-rules.php
- if you use `Shopsys\CodingStandards\CsFixer\Phpdoc\MissingParamAnnotationsFixer` in your ECS configuration, remove it
    - the fixer is no longer available
    - native type hints are now required instead of PHPDoc annotations for parameters
- if you use `Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer` in your ECS configuration, remove it
    - the fixer is no longer available
    - native type hints are now required instead of PHPDoc annotations for return types
- if you use `PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer` in your ECS configuration, remove it
    - the fixer is now redundant
    - the `SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff` is used instead
- if you use `PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer` in your ECS configuration, remove it
    - the fixer is redundant with the `DeclareStrictTypesSniff` included in `ShopsysSetList::SHOPSYS_CODING_STANDARD`

##### The following steps will need to be performed after all the upgrade steps from all tasks are done

- redundant PHPDoc annotations (that duplicate native type hints) are now automatically removed (thanks to `PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer`)
    - only annotations that provide additional value (like array contents or complex types) are preserved
- new coding standards are enforced by `SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff` and `SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff`
    - these sniffs require strict type hints in the whole codebase except entities, entity data objects, and a few particular interfaces used by the entities
    - most of the issues should be fixed automatically, however, you will need to address some issues manually
    - you can try cherry-picking relevant commits from `project-base` repository that could help you to fix the compilation errors caused by the parent-child class incompatibilities — see [add missing typehints to overridden methods](https://github.com/shopsys/project-base/commit/b4d0e62) and [tweaks in project-base ImageRepository](https://github.com/shopsys/project-base/commit/236cd70), or the full [#4396 sync](https://github.com/shopsys/project-base/commit/65504be) covering all changes
        - however, it is important to take into account that it might be difficult because of conflicts with your code
    - once it is possible, run `php phing standards-fix` to automatically fix the issues
    - it is possible that you will need to run the command multiple times
    - running `php phing phpstan` might help you with spotting the problematic places in the code
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/65504bee74cb4cd580405b6de95ac52b16b2001a) to update your project

#### Simplify Domain dependency ([#4417](https://github.com/shopsys/shopsys/pull/4417))

- `Domain` is now dependent on new, leaner `CurrentAdministrator` service instead of `AdministratorFacade` to prevent possible future `ServiceCircularReferenceException`.
- Removed `Domain` dependency from `AdministratorFacade` as it is no longer needed.
- The original `AdministratorFacade::getCurrentlyLoggedAdministrator()` method is kept for backwards compatibility, but using the leaner `CurrentAdministrator::getCurrentlyLoggedAdministrator()` is recommended if applicable for your use case.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dc94a732760cb4f39c44f3db1bced3956383ad77) to update your project

#### Refactor advanced search ([#4422](https://github.com/shopsys/shopsys/pull/4422))

- **Advanced search facades renamed and moved to domain-specific namespaces:**
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchProductFacade` -> `Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\ProductAdvancedSearchFacade`
    - `Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFacade` -> `Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\OrderAdvancedSearchFacade`
    - `Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\AdvancedSearchComplaintFacade` -> `Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\ComplaintAdvancedSearchFacade`
- **Advanced search filters moved to domain-specific namespaces:**
    - Order filters: `Shopsys\FrameworkBundle\Model\AdvancedSearchOrder\Filter\*` -> `Shopsys\FrameworkBundle\Model\Order\AdvancedSearch\Filter\*`
    - Complaint filters: `Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\Filter\*` -> `Shopsys\FrameworkBundle\Model\Complaint\AdvancedSearch\Filter\*`
    - Product filters: `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\*` -> `Shopsys\FrameworkBundle\Model\Product\AdvancedSearch\Filter\*`
- **`AdvancedSearchFilterInterface` moved and extended:**
    - interface moved from `Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFilterInterface` to `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface`
    - new required method `getLabel(): string` - returns translation key for filter label
    - new required static method `getEntityType(): string` - returns entity type identifier (e.g., 'product', 'order', 'complaint')
- **Config classes removed - filters now auto-registered via service tags:**
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig` removed
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchConfig` removed
    - `Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\ComplaintAdvancedSearchConfig` removed
    - use `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterRegistry` to get filters
- **Form factories consolidated into single class:**
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchFormFactory` removed
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\OrderAdvancedSearchFormFactory` removed
    - `Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\ComplaintAdvancedSearchFormFactory` removed
    - use `Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFormFactory` instead
- **Filter translation classes removed - filters now provide labels directly:**
    - `Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchOrderFilterTranslation` removed
    - `Shopsys\FrameworkBundle\Form\Admin\AdvancedSearch\AdvancedSearchProductFilterTranslation` removed
    - `Shopsys\FrameworkBundle\Model\AdvancedSearchComplaint\AdvancedSearchComplaintFilterTranslation` removed
    - filters now implement `getLabel()` method returning translation key
- **Method signature changes:**
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchQueryBuilderExtender::extendByAdvancedSearchData()` now requires new `string $entityType` parameter
    - `Shopsys\FrameworkBundle\Model\AdvancedSearch\AdvancedSearchFormFactory::createRulesForm()` now requires new `string $entityType` parameter
- **Customer user listing moved:**
    - `Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserListAdminFacade` -> `Shopsys\FrameworkBundle\Model\Customer\User\Listing\CustomerUserListAdminFacade`
    - `CustomerUserRepository::getCustomerUserListQueryBuilderByQuickSearchData()` moved to new `CustomerUserListAdminRepository`
- **New abstract base class for advanced search facades:**
    - extend `Shopsys\FrameworkBundle\Model\AdvancedSearch\AbstractAdvancedSearchFacade` for custom advanced search implementations
    - implement `getDefaultFilterName(): string` and `getEntityType(): string` methods
- **New abstract base class for filters:**
    - extend `Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AbstractAdvancedSearchFilter` for simpler filter implementations
- **Templates consolidated:**
    - entity-specific templates (`product/advancedSearch/`, `complaint/advancedSearch/`) removed or simplified
    - use extendable templates at `@ShopsysAdministration/content/advancedSearch/advancedSearch.html.twig` and `@ShopsysAdministration/content/advancedSearch/ruleForm.html.twig`
- if you have custom advanced search filters, update them to implement the new interface requirements (`getLabel()` and `getEntityType()`)
- if you extend advanced search facades, make them extend `AbstractAdvancedSearchFacade` and implement required abstract methods
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/81873ae449c15c5b4164a550aad61f31c117a208) to update your project

#### Frontend API is now always enabled ([#4423](https://github.com/shopsys/shopsys/pull/4423))

- `Shopsys\FrontendApiBundle\Component\Domain\EnabledOnDomainChecker` class has been removed without replacement
- Phing targets `frontend-api-enable` and `frontend-api-disable` have been removed
    - if you use these targets in your build scripts or CI/CD pipelines, remove them
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/939e4e4e5f5f8527b65ebfdde1b369040242358c) to update your project

#### Move image config from YAML to attributes ([#4421](https://github.com/shopsys/shopsys/pull/4421))

- if you have extended `\Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader`, you need to update your code

- the following classes have been removed:
    - `\Shopsys\FrameworkBundle\Component\Image\Config\Exception\EntityParseException`
    - `\Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition`

- if you have custom entities with image support configured in `images.yaml`, you need to migrate them to use the new PHP attributes:
    - add `#[EntityImage]` attribute to your entity class to define an image type
        - use `name` parameter to specify a custom type name (e.g., `#[EntityImage(name: 'gallery')]`), otherwise default name `default` will be used
        - use `multiple: true` to allow multiple images for the type (e.g., `#[EntityImage(multiple: true)]`)
        - the attribute is repeatable - you can add multiple `#[EntityImage]` attributes for different image types
    - add `#[EntityImageFolder(name: 'folderName')]` attribute to customize the folder name for storing images
        - if not specified, the folder name defaults to the camelCase class short name (e.g., `SalesRepresentative` → `salesRepresentative`)
    - example migration:

        ```yaml
        # Before (images.yaml)
        - name: product
          class: Shopsys\FrameworkBundle\Model\Product\Product
          multiple: true
        ```

        ```php
        // After (entity class)
        use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;

        #[EntityImage(multiple: true)]
        class Product extends AbstractTranslatableEntity
        ```

    - example with custom folder name and multiple image types:

        ```yaml
        # Before (images.yaml)
        - name: noticer
          class: Shopsys\FrameworkBundle\Model\Advert\Advert
          types:
              - name: web
              - name: mobile
        ```

        ```php
        // After (entity class)
        use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
        use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImageFolder;

        #[EntityImageFolder('noticer')]
        #[EntityImage]
        #[EntityImage('web')]
        #[EntityImage('mobile')]
        class Advert
        ```

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3c520ae87dd16c18a3aa2e28eba096a0bbbf87b9) to update your project

#### Resolved many deprecations to enable upgrade to Symfony 7 ([#4431](https://github.com/shopsys/shopsys/pull/4431))

- if you have implemented your own Doctrine event listeners, replace deprecated `Doctrine\ORM\Event\LifecycleEventArgs` with dedicated event classes:
    - `PreRemoveEventArgs` for `preRemove` events
    - `PrePersistEventArgs` for `prePersist` events
    - `PostPersistEventArgs` for `postPersist` events
    - `PostUpdateEventArgs` for `postUpdate` events
    - replace `$args->getEntity()` with `$args->getObject()`
    - replace `$args->getEntityManager()` with `$args->getObjectManager()`
- if you use Doctrine's `AbstractQuery::iterate()` method, replace it with `toIterable()`:
    - see [Doctrine ORM UPGRADE.md](https://github.com/doctrine/orm/blob/HEAD/UPGRADE.md#bc-break-removed-queryiterate) for details
- if you have customized Symfony Messenger, see [Symfony UPGRADE-7.0.md Messenger section](https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#messenger):
    - if you implemented handlers using `MessageHandlerInterface`, replace with `#[AsMessageHandler]` attribute
    - if you catch `HandlerFailedException` and use `getNestedExceptions()`, replace with `getWrappedExceptions()`
- if you use GuzzleHttp's `json_decode` or `json_encode` functions, replace with `Utils` methods:
    - `\GuzzleHttp\json_decode()` → `\Nette\Utils\Json::decode()`
    - `\GuzzleHttp\json_encode()` → `\Nette\Utils\Json::encode()`
    - see [Guzzle UPGRADING.md](https://github.com/guzzle/guzzle/blob/7.8/UPGRADING.md#removed-functionsphp) for details
- if you use League CSV's `getContent()` method, replace with `toString()`
- if you use `Symfony\Bridge\Monolog\Logger`, replace with `Monolog\Logger` directly:
    - see [Symfony UPGRADE-7.0.md MonologBridge section](https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#monologbridge) for details
- if you have your own migrations using `ContainerAwareInterface`, replace with specific AwareInterface:
    - available interfaces: `DomainAwareInterface`, `EntityManagerAwareInterface`, `AdministratorLocalizationAwareInterface`
    - if you need other services injected, create your own AwareInterface and extend `MigrationFactory`
    - see [Symfony UPGRADE-7.0.md DependencyInjection section](https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#dependencyinjection) for details
- if you use `Connection::PARAM_INT_ARRAY` or `Connection::PARAM_STR_ARRAY` in your queries or migrations, replace with `ArrayParameterType`:
    - `Connection::PARAM_INT_ARRAY` → `ArrayParameterType::INTEGER`
    - `Connection::PARAM_STR_ARRAY` → `ArrayParameterType::STRING`
    - see [Doctrine DBAL UPGRADE.md](hhttps://github.com/doctrine/dbal/blob/4.4.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants) for details
- if you use League CSV's `Writer::createFromString()`, replace with `Writer::fromString()`
- if you have your own Doctrine EventSubscribers, convert them to use `#[AsDoctrineListener]` attribute:
    - remove `implements EventSubscriber` and `getSubscribedEvents()` method
    - add `#[AsDoctrineListener(event: Events::eventName)]` attribute to the class
    - see [Symfony UPGRADE-7.0.md DoctrineBridge section](https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#doctrinebridge) for details
- the method `\Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository::getVatsWithoutProductsMarkedForDeletion` has been renamed to `getVatsMarkedForDeletionWithoutReferences` update your project if you were using it
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7e24d64135fac9001db5f47b9b4aa0224a18b7a3) to update your project.

#### Product recalculation deduplication improvements ([#4433](https://github.com/shopsys/shopsys/pull/4433))

- `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDeduplicationFacade::TTL` constant was changed from `10800` (3 hours) to `3600` (1 hour) override it in your project, if it does not suits your needs

#### Upgraded to Symfony 7.4 ([#4448](https://github.com/shopsys/shopsys/pull/4448))

This release upgrades Symfony from 6.4 to 7.4 and includes related dependency updates. For all Symfony-related changes in your custom code, refer to:

- [Symfony UPGRADE-7.0.md](https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.0.md)
- [Symfony UPGRADE-7.1.md](https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.1.md)
- [Symfony UPGRADE-7.2.md](https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.2.md)
- [Symfony UPGRADE-7.3.md](https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.3.md)
- [Symfony UPGRADE-7.4.md](https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.4.md)

The notes below cover Shopsys-specific changes:

- updated `overblog/graphql-bundle` to version 1.9 - if you have your own GraphQL input types with validation, change validation `groups` from string to array format:
    - before: `groups: "myGroup"`
    - after: `groups: ["myGroup"]`
- updated PHPUnit from ^11.0 to ^12.0 - if you have your own tests, you need to update them:
    - replace `@dataProvider` annotations with `#[DataProvider]` attributes
    - `createStub()` no longer supports `->with()` argument matchers (deprecated since PHPUnit 12.5.11, removed in PHPUnit 13) - use `createMock()` with `->expects()` instead, or use `->willReturnMap()` on the stub
    - `createMock()` without any configured expectations now triggers a notice (since PHPUnit 12.5) - use `createStub()` instead, or add a real expectation like `->expects($this->atLeastOnce())` / `->expects($this->never())`
    - for full details, see [PHPUnit 12 release announcement](https://phpunit.de/announcements/phpunit-12.html) and [PHPUnit 12.5 test doubles documentation](https://docs.phpunit.de/en/12.5/test-doubles.html)
- `phpunit.xml` now includes stricter settings for notices (`failOnNotice`, `failOnPhpunitNotice`, `displayDetailsOnTestsThatTriggerNotices`, `displayDetailsOnPhpunitNotices`) and deprecations (`failOnDeprecation`, `failOnPhpunitDeprecation`, `displayDetailsOnTestsThatTriggerDeprecations`, `displayDetailsOnPhpunitDeprecations`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1703f8c1796875c1d1741358d1216c85b604ae9d) to update your project
- see also #project-base-diff of [#4464](https://github.com/shopsys/shopsys/pull/4464) with additional fix

#### Remove draggable ([#4437](https://github.com/shopsys/shopsys/pull/4437))

- GrapeJS web editor now uses `nativeDnD: false` to prevent generating `draggable` HTML attributes in newly edited article content
- GrapeJS save command now sanitizes exported HTML and removes `draggable` attributes before persisting content
- Frontend API now sanitizes article and blog article `text` fields and strips `draggable` attributes from returned HTML
- if you have custom GrapeJS editor initialization, custom Frontend API query resolvers, or any other custom usage of GrapeJS type, apply equivalent sanitization there as well
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/80845c43595539a941a063603561db8fc4a28d4d) to update your project

#### Category tree responsive ([#4438](https://github.com/shopsys/shopsys/pull/4438))

- fixed responsive for category tree open/close button
- if your project includes more than two domains or locales, or reaches the maximum category level, it is recommended to use a two-column layout. To achieve this, customize the templates `packages/administration/templates/form/type/MultidomainType.html.twig` and `packages/administration/templates/form/type/LocalizedType.html.twig` by refining the `columnClass` logic.

#### Add ability to tab in select ([#4440](https://github.com/shopsys/shopsys/pull/4440))

- added the ability to tab through Tom Select in the administration

#### GoPay payment status notify endpoint is now handled by backend instead of storefront ([#4439](https://github.com/shopsys/shopsys/pull/4439))

- payment status notify endpoint has been moved from storefront to the backend as `Shopsys\FrameworkBundle\Controller\Front\PaymentStatusNotifyController`
    - if you have customized the `project-base/storefront/pages/order/payment-status-notify.tsx` storefront page, move your custom logic to the new backend controller
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c28b434bcd00e56e71d3de1c780bb9fac205341f) to update your project

#### remove unused ParameterWithValues class ([#4441](https://github.com/shopsys/shopsys/pull/4441))

- class `Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues` was removed
    - if you use this class in your project, replace it with plain arrays (the same structure returned by `Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory::createParametersArrayFromProductArray()`)
- method `create()` in `Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory` was removed
    - use `createParametersArrayFromProductArray()` with product data array instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/21f859adf0d5b385fa23bdd0fbaa1c56a0204c8a) to update your project

#### update phpunit xml schema versions ([#4435](https://github.com/shopsys/shopsys/pull/4435))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c3d7a20d58703c021cd2b26204a04838ca8e8818) to update your project

#### Mailer now supports Closures as variables ([#4443](https://github.com/shopsys/shopsys/pull/4443))

- all mail template classes in the framework package now define their variables as `\Closure` instances instead of plain `string` values for lazy evaluation
    - `Shopsys\FrameworkBundle\Model\Mail\Mailer::replaceVariables()` now handles both `string` and `\Closure` values
    - if you override any of the methods in `*Mail` classes that set `MessageData::$variablesReplacementsForBody` or `$variablesReplacementsForSubject`, your code will continue to work
    - however, we recommend updating them to also use `\Closure` for variable values to benefit from lazy evaluation
    - the same recommendation applies to any custom mail template classes in your project
    - the change is basically simple like this:
    ```diff
    - self::VARIABLE_NAME => $this->resolveVariable(),
    + self::VARIABLE_NAME => fn () => $this->resolveVariable(),
    ```
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/656e539a2a7ac52b7518f8c16b53b887535d11b8) to update your project

#### ensure friendly url slug is always url-encoded ([#4445](https://github.com/shopsys/shopsys/pull/4445))

- double-check `Shopsys\FrameworkBundle\Migrations\Version20260207120000` migration before running it on your production data
- check your custom code - if you insert `slug` into `friendly_url` DB table (other than the entity-way), or if you search by `slug` column, be sure to always use `FriendlyUrlSlugNormalizer::normalize()` on the slug value

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/79c55a4cb9254940afe6da76b335bdd21b47ce27) to update your project

#### ComplaintResolution: fix typo in property name ([#4446](https://github.com/shopsys/shopsys/pull/4446))

- `Shopsys\FrontendApiBundle\Component\Constraints\ComplaintResolution::$selecteComplaintResolutionRequiresBankAccountFilledMessage` property was renamed to `$selectedComplaintResolutionRequiresBankAccountFilledMessage` (typo fix)

#### Admin: customer user form simplification ([#4454](https://github.com/shopsys/shopsys/pull/4454))

- `Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType` domain field is now always display-only (`DisplayOnlyDomainIconType`)
    - the domain selector (`DomainType`) is no longer rendered when creating a new customer user
    - if your form type extension or custom code relies on the domain field being a selectable `DomainType`, update it to work with the display-only field instead
- password-email similarity constraints (`FieldsAreNotIdentical`, `NotIdenticalToEmailLocalPart`) have been removed from `Shopsys\FrameworkBundle\Form\Admin\Customer\User\CustomerUserFormType`
- `SelectToggle` admin JS component (`packages/framework/assets/js/admin/components/SelectToggle.js`) has been removed
    - if your project uses `data-js-toggle-opt-group-control` or `data-js-toggle-option` HTML attributes in custom form types, you need to implement the toggle functionality yourself
- constraints `Shopsys\FrameworkBundle\Form\Constraints\NotIdenticalToEmailLocalPart` and `Shopsys\FrameworkBundle\Form\Constraints\FieldsAreNotIdentical` along with their validtor classes were removed as unused

#### strip ImageFacade and ImageRepository of unnecessary code ([#4456](https://github.com/shopsys/shopsys/pull/4456))

- code was present for the purpose of allowing caching of whole image entity which is discouraged
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9c7f58b091173dc19e7808e794c190da2ba33a92) to update your project

#### security headers ([#4447](https://github.com/shopsys/shopsys/pull/4447))

- classes `Shopsys\FrameworkBundle\Component\HttpFoundation\ResponseListener` and `Shopsys\\FrameworkBundle\\Component\\Domain\\DomainAwareSecurityHeadersSetter` were removed
    - use `Shopsys\FrameworkBundle\Component\HttpFoundation\SecurityHeadersResponseListener` instead
- be sure to double-check `Shopsys\FrameworkBundle\Migrations\Version20260208091423` and `Shopsys\FrameworkBundle\Migrations\Version20260218113001` migrations that set and sanitize Content-Security-Policy value
- you need to apply the following changes in your `app/orchestration/kubernetes/configmap/nginx.yaml` file
    - see https://gist.github.com/ShopsysBot/b51e2ef25d79e57d640958679f4c75a1
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9dad7a39d3ca1b377efa47b08ca276c722dd89ad) to update your project

#### enable launching application without Sentry and CDN ([#4458](https://github.com/shopsys/shopsys/pull/4458))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7297c9b4d943cc60383eef2ac42c950d2fee2e4b) to update your project

#### update nginx to the latest version ([#4399](https://github.com/shopsys/shopsys/pull/4399))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bbfe65bb25839e7879095dd53896c623d3716aeb) to update your project

#### remove usage of CustomerFileException ([#2637](https://github.com/shopsys/shopsys/pull/2637))

- `Shopsys\FrameworkBundle\Component\CustomerUploadedFile\Exception\CustomerFileException` was removed
- use `CustomerFileNotFoundException` instead, or implement custom exception, if your code relies on the `CustomerFileException`

#### Performance refactor ([#4460](https://github.com/shopsys/shopsys/pull/4460))

- added `itemsCount` field to the `ProductList` GraphQL type (returns item count without fetching full product data)
- removed `getProductsCount()` method from `ProductList` entity — use `getItemsCount()` instead
- added `384` to the allowed image sizes in `imageResizer.php` to align backend resizer and Next.js config (`project-base/app/web/imageResizer.php`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0ad7b89eab24981773766b32bed1e21991013a2f) to update your project

#### phpstan: move particular file ignores from configs to inline suppressions ([#4466](https://github.com/shopsys/shopsys/pull/4466))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f7d65e1b931c94534c70c389f7014814bc0a6bd1) to update your project

#### PHP 8.5 compatibility ([#4470](https://github.com/shopsys/shopsys/pull/4470))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9b11fa5472c13fd56686be0641873833c7269459) to update your project
- for reference, see [PHP 8.4 migration guide](https://www.php.net/manual/en/migration84.php) and [PHP 8.5 migration guide](https://www.php.net/manual/en/migration85.php)

#### replace `litipk/php-bignumbers` with `brick/math` ([#4484](https://github.com/shopsys/shopsys/pull/4484))

- `litipk/php-bignumbers` dependency was removed and replaced with `brick/math`
    - if you have used `Litipk\BigNumbers\Decimal` in your code, you need to replace it with `Brick\Math\BigDecimal`
    - see [Brick\Math](https://github.com/brick/math) for details
- if you extend `Shopsys\FrameworkBundle\Component\Money\Money`:
    - the protected property `$decimal` type changed from `Litipk\BigNumbers\Decimal` to `Brick\Math\BigDecimal`
    - the return type of the protected static method `createDecimal()` changed accordingly
    - update any code that accesses `$this->decimal` directly to use the `Brick\Math\BigDecimal` API
- `Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade::getExchangeRateForCurrencies()` now returns `Brick\Math\BigDecimal` instead of `Litipk\BigNumbers\Decimal`
- `Money::divide()` now explicitly throws `\DomainException` when dividing a non-zero value by zero

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3c2d82a49a8721e86d69ea0582981d1f8bfdded9) to update your project

#### FE API: rollback mutation & discard async messages on error ([#4479](https://github.com/shopsys/shopsys/pull/4479))

- `Shopsys\FrameworkBundle\Component\HttpFoundation\TransactionalMasterRequestListener::setTransactionManuallyRollbacked()` was removed
    - if you need to enforce transaction rollback in your code, you can dispatch `Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent` instead
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8eba03ebfefff54881ef71dd745c93a18e105137) to update your project

#### Improve gift data fixtures ([#4483](https://github.com/shopsys/shopsys/pull/4483))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b5201bd8dae4c8b08a376b691e3af3968a36d7a3) to update your project

#### Refactor query builder joins ([#4426](https://github.com/shopsys/shopsys/pull/4426))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/356ccb12ccd53fa2d7901808dfaca612e1c26ec6) to update your project

#### remove obsolete Symfony type-info patch ([#4485](https://github.com/shopsys/shopsys/pull/4485))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/419cfbd0358ac7d8162bebc0d9ff79a1695c6798) to update your project

#### add reference.php to gitignore ([#4489](https://github.com/shopsys/shopsys/pull/4489))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/870fe8d7136d163c5da34aa4db5e90cefc745f45) to update your project

#### Add blog article status ([#4490](https://github.com/shopsys/shopsys/pull/4490))

- property `$hidden` in `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle` was removed along with the `isHidden()` method
    - use `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle::getStatus(int $domainId)` instead and compare against `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleStatusEnum::STATUS_DRAFT` or `BlogArticleStatusEnum::STATUS_PUBLISHED`
- property `$publishDate` in `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle` was removed from the entity level and moved to `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDomain`
    - `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle::getPublishDate()` method now requires `int $domainId` parameter
- properties `$hidden` and `$publishDate` in `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleData` were removed
    - use `$statuses` (indexed by domain ID, values from `BlogArticleStatusEnum`) instead of `$hidden`
    - use `$publishDates` (indexed by domain ID) instead of `$publishDate`
- if you have overridden `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository::getVisibleBlogArticlesByDomainIdAndLocaleQueryBuilder()`, update your implementation to filter by `bad.status` and `bad.publishDate` on `BlogArticleDomain` instead of `ba.hidden` and `ba.publishDate` on `BlogArticle`
- if you have overridden `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleVisibilityRepository::refreshBlogArticlesVisibilityOnDomain()`, update your implementation to use `bad.status = :status` instead of `ba.hidden = FALSE`
- if you have customized `Shopsys\FrameworkBundle\Form\Admin\Blog\BlogArticleFormType`, the `hidden` field (YesNoType) and single `publishDate` field (DatePickerType) have been replaced by `statuses` (MultidomainType with ChoiceType) and `publishDates` (MultidomainType with DatePickerType)
- blog article status transitions are now managed by a Symfony Workflow state machine (`blog_article_publishing`)
    - copy `project-base/app/config/packages/workflow.yaml` to your project to register the workflow
    - the workflow defines three places (`draft`, `preview`, `published`) and three transitions (`publish`, `to_preview`, `to_draft`) on `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDomain`
    - `Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticlePublishingGuardSubscriber` guards the `publish` transition — it blocks publishing if the article name is not defined for the domain's locale
    - if you need custom transition guards or additional statuses, extend the workflow configuration and/or subscribe to `workflow.blog_article_publishing.guard.*` events
- blog articles in `preview` status are accessible via direct URL but excluded from listings and sitemaps — the storefront should render `noindex, nofollow` for such articles
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1f4f6410e15fbbe5f1ff936c489cb9199d65471d) to update your project
- see also #project-base-diff of https://github.com/shopsys/shopsys/pull/4490 with an additional fix

#### reworked cron runner ([#4461](https://github.com/shopsys/shopsys/pull/4461))

- `Shopsys\FrameworkBundle\Component\Cron\CronFacade::runModuleByServiceId()` was removed
    - if you call this method directly, replace it with `CronFacade::runSingleModule()`:

    ```diff
    -$cronFacade->runModuleByServiceId($serviceId);
    +$cronFacade->runSingleModule($serviceId, $instanceName, $processOutputCallback, $isOutputDecorated);
    ```

- `Shopsys\FrameworkBundle\Component\Cron\CronFacade::runScheduledModulesForInstance()` now requires two additional parameters `callable $processOutputCallback` and `bool $isOutputDecorated`:

    ```diff
    -$cronFacade->runScheduledModulesForInstance($instanceName);
    +$cronFacade->runScheduledModulesForInstance($instanceName, $processOutputCallback, $isOutputDecorated);
    ```

- `Shopsys\FrameworkBundle\Command\CronCommand::runCron()` protected method was removed — if you extend `CronCommand` and override `runCron()`, migrate to the new methods `acquireInstanceMutex()` and `runModulesInInstance()`

- new `stop_on_failure` configuration option added to cron instances in `cron.yaml` — the default when omitted is `true` (cron instance stops after the first module failure),
  set it to `false` for instances whose modules are independent of each other and should not block subsequent modules from running:

    ```diff
     parameters:
         cron_instances:
             export:
                 run_every_min: 5
                 timeout_iterated_cron_sec: 240
    +            stop_on_failure: false
    ```

- see #project-base-diff

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/66398cee1f1d489df82caaf46c2064811485c4d2) to update your project

#### Heureka categories for SK domains ([#4216](https://github.com/shopsys/shopsys/pull/4216))

- `Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryData::$id` property was renamed to `$heurekaId`
- `Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade` methods have been updated to support multiple locales:
    - `findByCategoryId(int $categoryId)` was removed, use `findByCategoryIdAndLocale(int $categoryId, string $locale)` instead
    - `getOneById(int $id)` was removed, use `getOneByHeurekaIdAndLocale(int $heurekaId, string $locale)` instead
    - `getAllIndexedById()` was removed, use `getAllIndexedByHeurekaId(string $locale)` instead
    - `saveHeurekaCategories()`, `changeHeurekaCategoryForCategoryId()`, and `removeHeurekaCategoryForCategoryId()` now require a new `string $locale` parameter
- `Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryRepository` methods were updated similarly:
    - `getAllIndexedById()` was removed, use `getAllIndexedByHeurekaId(string $locale)` instead
    - `findByCategoryId(int $categoryId)` was removed, use `findByCategoryIdAndLocale(int $categoryId, string $locale)` instead
    - `getOneById(int $id)` was removed, use `getOneByHeurekaIdAndLocale(int $heurekaId, string $locale)` instead
- `Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryDownloader::getHeurekaCategories()` now requires a new `string $locale` parameter
- if you have extended `Shopsys\ProductFeed\HeurekaBundle\Model\FeedItem\HeurekaFeedItemFactory` and overridden `findHeurekaCategoryFullNameByCategoryIdUsingCache()` or `findHeurekaCategoryFullNameByCategoryId()`, update their signatures to include the new `string $locale` parameter
- the `shopsys.product_feed.heureka_bundle.heureka_category_feed_url` configuration parameter was removed; if you override it in your project, replace it with `shopsys.product_feed.heureka_bundle.heureka_category_feed_cz_url` and `shopsys.product_feed.heureka_bundle.heureka_category_feed_sk_url`

#### Tom Select dropdown in modals now renders correctly ([#4497](https://github.com/shopsys/shopsys/pull/4497))

- dropdown was appended to body, rendering behind the modal due to z-index and focus trap conflicts

#### improve local CDN testing instructions ([#4499](https://github.com/shopsys/shopsys/pull/4499))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/67c9b392075890b59cb5ec1de0fde2cb0eee9159) to update your project

#### Remove order transport and payment relations ([#4473](https://github.com/shopsys/shopsys/pull/4473))

- properties `$transport` and `$payment` in `Shopsys\FrameworkBundle\Model\Order\Order` have been removed
    - the methods `Order::getTransport()` and `Order::getPayment()` still exist and now delegate to the transport/payment order items
    - if your code accesses `$order->transport` or `$order->payment` directly, use `$order->getTransport()` and `$order->getPayment()` instead
- properties `$transport` and `$payment` in `Shopsys\FrameworkBundle\Model\Order\OrderData` have been removed
    - use `$orderData->orderTransport->transport` and `$orderData->orderPayment->payment` instead
    - if you have custom `OrderProcessorMiddleware` implementations that read or write `$orderData->transport` or `$orderData->payment`, update them to use the order item data path
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7f458f596cd82ec6704b1afb67a9c9b7812eb83c) to update your project

#### Localize category SEO slugs ([#4471](https://github.com/shopsys/shopsys/pull/4471))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/aedf1cbc3553781cd3dcaee40cfb00185415f941) to update your project

#### Unify top product fixtures between domains ([#4472](https://github.com/shopsys/shopsys/pull/4472))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/3f84d1ef28ef506e1c14b0166ed929c944b83f4f) to update your project

#### Add SEO attributes as search keywords ([#4413](https://github.com/shopsys/shopsys/pull/4413))

- Elasticsearch indexes for `article`, `blog_article`, and `product` need to be recreated after upgrading due to new field mappings and changed analyzer settings
- if you have customized Elasticsearch definitions in your project, update them to include the new `searching_seo_titles`, `searching_seo_h1s`, and `searching_seo_meta_descriptions` field mappings for product indexes, and enhanced analyzers for `seoH1`, `seoTitle`, `seoMetaDescription` fields in article and blog_article indexes
- if your project uses Luigi's Box, after the feeds are regenerated, contact Luigi's Box support and ask them to include the new SEO elements (`seoTitle`, `seoMetaDescription`, `seoH1`) in search with partial matching enabled
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7564d8d6f5dc9534da68bee483dc168692ad567b) to update your project

#### Order relations that can influence order item price calculations are now stored during order creation ([#4507](https://github.com/shopsys/shopsys/pull/4507))

- `Shopsys\FrameworkBundle\Model\Order\Order::getCurrency()` method was removed, use `getCurrencyCode()`, `getCurrencyRoundingType()`, `getCurrencyRoundingPlacesPriceWithoutVat()`, `getCurrencyMinFractionDigits()`, and `isPaymentCzkRounding()` instead
- `Shopsys\FrameworkBundle\Model\Order\OrderData::$currency` property was removed, use `$currencyCode`, `$currencyRoundingType`, `$currencyRoundingPlacesPriceWithoutVat`, `$currencyMinFractionDigits`, and `$paymentCzkRounding` properties instead or use `OrderData::fillCurrencyFieldsFromCurrency(Currency $currency)` to populate all currency fields at once
- `Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory::createByLocaleAndCurrency()` was renamed to `createByLocaleAndMinFractionDigits(string $locale, int $minFractionDigits)`
- `Shopsys\FrameworkBundle\Model\Pricing\Rounding` method signatures changed:

    | Old method                                     | New method                                         |
    | ---------------------------------------------- | -------------------------------------------------- |
    | `roundPriceWithVatByCurrency(Money, Currency)` | `roundPriceWithVat(Money, string $roundingType)`   |
    | `roundPriceWithoutVat(Money, Currency)`        | `roundPriceWithoutVat(Money, int $roundingPlaces)` |
    | `roundVatAmount(Money, Currency)`              | `roundVatAmount(Money, int $roundingPlaces)`       |

- `Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation` method signatures changed:

    | Old method                                                        | New method                                                                   |
    | ----------------------------------------------------------------- | ---------------------------------------------------------------------------- |
    | `getVatAmountByPriceWithVat(Money, Vat, Currency)`                | `getVatAmountByPriceWithVat(Money, Vat, int $roundingPlaces)`                |
    | `getVatAmountByPriceWithVatForVatPercent(Money, float, Currency)` | `getVatAmountByPriceWithVatForVatPercent(Money, float, int $roundingPlaces)` |

- `Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation::calculateRoundedBasePrice()` — parameter `Currency $currency` replaced by `string $roundingType, int $roundingPlaces`
- `Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation` method signatures changed:
    - `calculatePrice()` — `Currency $currency` parameter removed, new `string $roundingType` and `int $roundingPlaces` parameters added at end
    - `calculateIndependentPrice()` — `Currency $currency` parameter removed, signature is now `(Payment $payment, int $domainId, string $roundingType, int $roundingPlaces)`
- `Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation::calculateOrderRoundingPrice()` — parameters changed from `(Payment $payment, Currency $currency, PriceInterface $orderTotalPrice)` to `(bool $paymentCzkRounding, string $currencyCode, string $currencyRoundingType, PriceInterface $orderTotalPrice)`
- `Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation` method signatures changed:
    - `calculatePercentageDiscountRoundedByCurrency()` — `Currency $currency` replaced by `string $roundingType, int $roundingPlaces`
    - `calculateNominalDiscount()` — `Currency $currency` replaced by `int $roundingPlaces`
- `Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation` method signatures changed:
    - `calculatePriceWithoutVatForInputPriceWithVat()` — new required third parameter `int $roundingPlaces`
    - `calculatePriceWithVatForInputPriceWithoutVat()` — third parameter changed from `Currency $currency` to `string $currencyRoundingType`
- `Shopsys\FrameworkBundle\Model\Payment\PaymentFacade::getPaymentPricesWithVatByCurrencyAndDomainIdIndexedByPaymentId()` was renamed to `getPaymentPricesWithVatByDomainIdIndexedByPaymentId(int $domainId, string $roundingType, int $roundingPlaces)`, `Currency $currency` parameter replaced by explicit `string $roundingType` and `int $roundingPlaces`
- `Shopsys\FrameworkBundle\Model\Payment\PaymentInstructionFacade::createSpdString()` — fourth parameter changed from `Currency $currency` to `string $currencyCode`
- Twig filter `priceWithCurrency(order.currency)` was renamed to `priceWithCurrencyByOrder(order)`, update your templates accordingly
- Twig filter `priceTextWithCurrencyByCurrencyIdAndLocale(order.currency.id, locale)` was renamed to `priceTextWithCurrencyByOrderAndLocale(order, locale)`, update your templates accordingly
- Twig filter `currencySymbolByCurrencyId(order.currency.id)` was renamed to `currencySymbolByCode(order.currencyCode)`, update your templates accordingly
- database migration `Version20260310120000` adds new columns to the `orders` table and migrates data from `currencies` and `payments` — this may take a significant amount of time on databases with a large number of orders
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/43babc1062055997a2a11c216df7b77f962b13b2) to update your project

#### Improved error handling and logging in image resizer ([#4504](https://github.com/shopsys/shopsys/pull/4504))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2ba8c6cca88bdc45a1fb7fff789914509ca5abd8) to update your project

#### fix annotations for getTranslations() method in all translatable entities ([#3203](https://github.com/shopsys/shopsys/pull/3203))

- in your translatable entities (i.e., entities extending `Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity`), you should use the following annotations:
    - `@method \Doctrine\Common\Collections\Collection<string, \App\YourNamespace\YourEntityTranslation> getTranslations()` above the entity class
    - `@var \Doctrine\Common\Collections\Collection<string, \App\YourNamespace\YourEntityTranslation>` above the `protected $translations` property
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/980df7c55267d632062420b2e92ba208de632122) to update your project

#### tweak FilterQueryTest::testParameters() and testFlagBrand() ([#3734](https://github.com/shopsys/shopsys/pull/3734))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/745de0f480ae405ea4ade1a3d3f11447a7355b0a) to update your project

#### CKeditor fixes ([#4506](https://github.com/shopsys/shopsys/pull/4506))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c878440cf626e8760f33ead1faa7bc58a882bd9a) to update your project

#### move ProductEntityFieldMapper::getFlags() from project-base to the frontend-api package ([#4509](https://github.com/shopsys/shopsys/pull/4509))

- [features moved](#movement-of-features-from-project-base-to-packages) from project-base to the frontend-api package:
    - `getFlags()` method from `App\FrontendApi\Resolver\Products\DataMapper\ProductEntityFieldMapper` to `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/07deb089d1aa229b5a2260b39fb45eef264eab71) to update your project

#### recalculations: introduce constants for "all scopes" and "all fields" ([#4511](https://github.com/shopsys/shopsys/pull/4511))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8168aee0f98ab427c4a28740ac97c2d30bd54969) to update your project

#### Doctrine ORM 3 + DBAL 4 + DoctrineBundle 3 ([#4513](https://github.com/shopsys/shopsys/pull/4513))

- upgrade your Doctrine dependencies and review the official upgrade guides for all breaking changes:
    - [Doctrine ORM UPGRADE.md](https://github.com/doctrine/orm/blob/3.6.x/UPGRADE.md) (ORM 2.x → 3.x)
    - [Doctrine DBAL UPGRADE.md](https://github.com/doctrine/dbal/blob/4.4.x/UPGRADE.md) (DBAL 3.x → 4.x)
    - [DoctrineBundle UPGRADE-3.0.md](https://github.com/doctrine/DoctrineBundle/blob/3.3.x/UPGRADE-3.0.md)
- the most probable changes needed in your project:
    - replace `$query->execute()` with `->getResult()`, `->getScalarResult()`, or `->getArrayResult()` depending on the hydration mode
    - replace `$connection->executeQuery()` with `$connection->executeStatement()` for DDL/DML statements (INSERT, UPDATE, DELETE, CREATE, ALTER, DROP)
    - replace `$queryBuilder->setParameters([...])` with chained `->setParameter('name', $value)` calls
    - replace `Doctrine\ORM\Mapping\ClassMetadataInfo` with `Doctrine\ORM\Mapping\ClassMetadata`
    - if you write custom migrations, use `$this->sql()` for DDL/DML and `$this->sqlQuery()` for SELECT queries that return data
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7d1d0ef0d336780074d82953164eb52d292bb790) to update your project

#### Simplify category SEO creation ([#4419](https://github.com/shopsys/shopsys/pull/4419))

- method `readyCombinationButtonAction` in `Shopsys\FrameworkBundle\Controller\Admin\CategorySeoController` was removed along with its template `@ShopsysAdministration/content/categorySeo/readyCombinationEditButton.html.twig`
    - if you override `CategorySeoController` and call `readyCombinationButtonAction()`, remove that call; the combination selector is now rendered directly via `{{ form(combinationSelectorForm) }}` in the `newCombinations.html.twig` template
    - if you override the `readyCombinationEditButton.html.twig` template, remove the override
- if you extend `Shopsys\FrameworkBundle\Controller\Admin\CategorySeoController` and override the constructor, add the new `Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade $productListOrderingModeForListFacade` parameter and pass it to `parent::__construct()`

#### Move default stock to domain ([#4517](https://github.com/shopsys/shopsys/pull/4517))

- the concept of a single global default stock has been replaced by a per-domain default stock
    - the `isDefault` column has been moved from the `stocks` table to `stock_domains` via migration `Version20260318194239`
- property `\Shopsys\FrameworkBundle\Model\Stock\Stock::$isDefault` has been removed
    - use `\Shopsys\FrameworkBundle\Model\Stock\Stock::isDefault(int $domainId)` to check default status for a specific domain
    - use `\Shopsys\FrameworkBundle\Model\Stock\Stock::isDefaultOnAnyDomain()` to check if the stock is default on any domain
    - other default-related properties and methods were replaced or removed
- route `admin_stock_setdefault` and `\Shopsys\FrameworkBundle\Controller\Admin\StockController::setDefaultAction()` have been removed
    - the default warehouse is now configured per domain via the stock edit form
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/80acd01e32f379035bbcb908e0cdcb8b26cdbfb8) to update your project

#### removed dead code ([#4515](https://github.com/shopsys/shopsys/pull/4515))

- class `Shopsys\FrameworkBundle\Component\Microservice\MicroserviceClient` was removed, if you use or extend this class, implement your own HTTP client for microservice communication
- class `Shopsys\FrameworkBundle\Model\Order\Watcher\TransportAndPaymentCheckResult` was removed, if you use or extend this class, implement your own replacement
- class `Shopsys\FrameworkBundle\Model\Order\Withdrawal\Exception\OrderNotFoundForWithdrawalException` was removed, if you catch or throw this exception, use a different exception class

#### Improve work with phone numbers ([#4514](https://github.com/shopsys/shopsys/pull/4514))

- phone numbers across all entities are now stored as structured data using `Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData` instead of plain strings
    - the `$telephone` property on `OrderData`, `CustomerUserData`, `DeliveryAddressData`, `ComplaintData` (`$deliveryTelephone`), `InquiryData`, `SalesRepresentativeData`, `WithdrawalRequestData`, and `RegistrationData` now expects `PhoneData` instead of `string`
    - if you set telephone data directly on these data objects, use `new PhoneData($countryCode, $prefix, $number)` instead of a plain string
    - existing `getTelephone()` methods on entities now returns a formatted `string`; if you need to get the raw phone number string, you can use `getTelephoneData() directly and format it as needed
- GraphQL input fields `telephone` and `deliveryTelephone` on `OrderInput`, `TelephoneInputObject`, `DeliveryAddressInputObject`, and `OrderWithdrawalRequestInput` now expect `PhoneDataInput` type (with `countryCode`, `prefix`, and `number` fields) instead of a plain `String`
    - update all storefront mutations that send phone numbers to use the new structured format
- new `PhoneData` and `PhoneDataInput` GraphQL types were added, and new `telephoneData` fields were added to `Order`, `BaseCustomerUser`, `DeliveryAddress`, `Complaint`, `SalesRepresentative`, and `OrderWithdrawalRequest` types
- new `Settings#phonePrefixes` query returns available phone prefixes for the current domain
- new `PhoneType` form type was added for administration phone number inputs with country dial code selector
- **database migration warning**: the included migration (`Version20260312111907`) performs an in-place transformation of existing phone number data across multiple tables (`orders`, `delivery_addresses`, `customer_users`, `complaints`, `sales_representatives`, `inquiries`, `withdrawal_requests`) — it splits previously stored full phone strings into separate prefix and number columns using `libphonenumber`
    - **back up your database before running this migration**, as the transformation is irreversible
    - phone numbers that cannot be parsed by `libphonenumber` will keep the original value in the number column; if the number does not start with `+`, a default prefix based on the associated country will be applied
    - if your project stores phone numbers in custom tables or columns beyond those listed above, you will need to write your own migration to handle them
    - the migration processes each phone number row individually (SELECT + UPDATE per row), so it may take a significant amount of time on large datasets — especially the `orders` and `customer_users` tables; plan for maintenance downtime accordingly
    - after running the migration, verify that phone numbers were correctly split, especially if your data contains non-standard or local phone number formats
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/40ded8e76d42bd51da3787ab2ba191ed3f41311b) to update your project

#### Minor fixes related to clean installation ([#4519](https://github.com/shopsys/shopsys/pull/4519))

- constant `Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat::SETTING_DEFAULT_VAT` has been removed, use `Shopsys\FrameworkBundle\Model\Pricing\Vat\VatSetting` class instead to get/set the default VAT ID
- method `Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade::getCount()` has been removed, if you use this method, implement your own count query
- method `Shopsys\FrameworkBundle\Model\Product\Unit\UnitRepository::getCount()` has been removed, if you use this method, implement your own count query
- `Shopsys\FrameworkBundle\Twig\RequiredSettingExtension` methods `checkAtLeastOneUnitExists()` and `checkDefaultUnitIsSet()` have been removed, if you extend this class or call these methods, remove the related logic as the default unit is now created via database migration
- translation key `New` for order and complaint statuses has been changed to `New [adjective]` to support specific translations, if you have custom translations for these statuses, update the translation key accordingly
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a4cb203a16b4d92426f65eeee3828d8e3c44a3ac) to update your project

#### Added new rounding to 0.05 for order total price ([#4522](https://github.com/shopsys/shopsys/pull/4522))

- replace usages of `\Shopsys\FrameworkBundle\Model\Payment\PaymentData::$czkRounding` (bool) with `\Shopsys\FrameworkBundle\Model\Payment\PaymentData::$orderRoundingTypeByDomainId` (string[] indexed by domain ID) using values from `\Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum` (`none`, `five_cents`, `whole`)
- replace `\Shopsys\FrameworkBundle\Model\Payment\Payment::isCzkRounding()` with `\Shopsys\FrameworkBundle\Model\Payment\Payment::hasOrderRoundingForDomain(int $domainId)` and `\Shopsys\FrameworkBundle\Model\Payment\Payment::getOrderRoundingTypeForDomain(int $domainId)`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/733f457aadcc1dacfa7329554b857eb467aa49dc) to update your project

#### Cron now uses standard crontab syntax for specifying run intervals ([#4523](https://github.com/shopsys/shopsys/pull/4523))

- `Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface` methods `getTimeHours()` and `getTimeMinutes()` have been replaced by `getCronExpression()` method
    - if you implement this interface, replace both methods with `getCronExpression()` returning a 5-field crontab expression (e.g., `'0 3 * * *'`)
- `Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig` constructor parameters `$timeHours` and `$timeMinutes` have been replaced by a single `$cronExpression` parameter
    - if you instantiate this class directly, update the constructor call accordingly
- `Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig::getReadableFrequency()` now accepts an optional `string $locale` parameter
    - if you override this method, update your signature to include the optional `string $locale = 'en'` parameter
- `Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig::registerCronModuleInstance()` parameters `$timeHours` and `$timeMinutes` have been replaced by a single `$cronExpression` parameter
    - if you call this method, update the arguments accordingly
- `Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver::validateTimeString()` has been renamed to `Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver::validateCronExpression()`
    - if you call this method, rename the method call
- `Shopsys\FrameworkBundle\Component\Cron\Config\Exception\InvalidTimeFormatException` has been removed
    - if you catch this exception, catch `\InvalidArgumentException` instead
- `Shopsys\FrameworkBundle\Model\Feed\FeedConfig` constructor parameters `$hours` and `$minutes` have been replaced by a single `$cronExpression` parameter
    - `getTimeHours()` and `getTimeMinutes()` methods have been replaced by `getCronExpression()`
    - if you extend this class or call these methods, update your code accordingly
- `Shopsys\FrameworkBundle\Model\Feed\FeedRegistry::registerFeed()` parameters `$timeHours` and `$timeMinutes` have been replaced by a single `$cronExpression` parameter
    - if you call this method, update the arguments accordingly
- service tag format for `shopsys.cron` and `shopsys.feed` has changed from `hours`/`minutes` attributes to a single `cron` attribute with a 5-field crontab expression
    - if you register custom cron modules or feeds via service tags, update the tag attributes (e.g., `{ hours: '3', minutes: '0' }` → `{ cron: '0 3 * * *' }`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/00091ce958e988fdea5c2ead1b986890ce538bc5) to update your project

#### removed invalid annotations ([#4526](https://github.com/shopsys/shopsys/pull/4526))

- run `php phing annotations-fix` to remove stale `@method` and `@property` annotations from extended classes

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1353d0618fc41fbda974a620723e47644098d0f3) to update your project

#### [GrapesJS] fix cross-domain template loading blocked by CSP ([#4531](https://github.com/shopsys/shopsys/pull/4531))

- Twig function `getDomainUrlByLocale` has been removed from `\Shopsys\FrameworkBundle\Twig\DomainExtension` along with its backing method
    - if you used this Twig function in your templates, replace it with your own implementation or use the existing `getDomain` / `getFirstDomain` Twig functions instead

#### Fixed demo data payment transaction statuses to prevent cron errors ([#4532](https://github.com/shopsys/shopsys/pull/4532))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fadc7fb831436d081273c751ca1bee2799c7830b) to update your project

#### Fixed project-base build after removal of publicRuntimeConfig ([#4534](https://github.com/shopsys/shopsys/pull/4534))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ef565ac62d5c1a3190e6c1c2f811669b4ad55105) to update your project

#### Parameter values are now truly unique on database level ([#4536](https://github.com/shopsys/shopsys/pull/4536))

- migration `Shopsys\FrameworkBundle\Migrations\Version20260401064643` creates a unique index on the `parameter_values` table for columns `(locale, text)`
    - before running the migration, check for duplicates and resolve them manually
    - you can use this SQL query to find duplicates:
    ```sql
        SELECT
            text,
            locale,
            COUNT(*) AS duplicate_count,
            string_agg(COALESCE(numeric_value::text, 'NULL'), ', ' ORDER BY id) AS numeric_values,
            string_agg(COALESCE(rgb_hex, 'NULL'), ', ' ORDER BY id) AS rgb_hex_values,
            string_agg(id::text, ', ' ORDER BY id) AS ids
        FROM parameter_values
        GROUP BY text, locale
        HAVING COUNT(*) > 1
        ORDER BY duplicate_count DESC, locale ASC, text ASC;
    ```
- method `ParameterRepository::findParameterValueByValueTextNumericValueAndLocale()` has been renamed to `findParameterValueByValueTextAndLocale()` and no longer accepts `$numericValue` parameter
- method `ParameterRepository::getParameterValueByValueTextNumericValueAndLocale()` has been renamed to `getParameterValueByValueTextAndLocale()` and no longer accepts `$numericValue` parameter
- method `ParameterFacade::findParameterValueByValueTextNumericValueAndLocale()` has been renamed to `findParameterValueByValueTextAndLocale()` and no longer accepts `$numericValue` parameter
- method `ParameterFacade::getParameterValueByValueTextNumericValueAndLocale()` has been renamed to `getParameterValueByValueTextAndLocale()` and no longer accepts `$numericValue` parameter
- method `ParameterTransactionFunctionalTestCase::getParameterValueIdForFirstDomain()` no longer accepts `$isNumeric` parameter

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a3b8690130298a4a5831ab85469e3d61c5f2ed4a) to update your project

#### Uploaded files can be viewed directly in browser ([#3589](https://github.com/shopsys/shopsys/pull/3589))

- rename calls to `Shopsys\FrameworkBundle\Component\AbstractUploadedFile\AbstractUploadedFile::setTemporaryFilename()` and `Shopsys\FrameworkBundle\Component\Image\Image::setTemporaryFilename()` to `updateFile()` and pass the file size as a second argument (use `Shopsys\FrameworkBundle\Component\FileUpload\FileUpload::getTemporaryFilesize()` to compute it)
- update `shopsys/deployment` bundle to the latest version
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/56862ba98ec0a23ea6e29c8c948402ec9efb420f) to update your project

#### fix GetStoreTest reliability around midnight ([#4551](https://github.com/shopsys/shopsys/pull/4551))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a89bb1259290ddb3123fefab0fd342f0f5f08553) to update your project

#### token auth errors now preserve expired-token across backend and storefront ([#4560](https://github.com/shopsys/shopsys/pull/4560))

- Frontend API token authentication failures are now returned with HTTP code 200 instead of previous 401 code with GraphQL error `extensions.userCode` values `expired-token` and `invalid-token`, update your application to reflect it

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d5f3893e7407463eacec14ea734aa1325c0f1b2f) to update your project

#### add test to confirm promo code limits/flags edit works properly ([#4562](https://github.com/shopsys/shopsys/pull/4562))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0fbddfbc5fa7579f55b115db2b147f577acc5d32) to update your project

#### [CRUD] fix translations ([#4527](https://github.com/shopsys/shopsys/pull/4527))

- method `getItems()` in `Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry` was renamed to `getAll()` and now returns `Shopsys\AdministrationBundle\Component\Crud\CrudRegistryItem[]` instead of `Shopsys\AdministrationBundle\Component\Crud\Definition[]`
    - access config via `$item->config` property, controller class via `$item->controllerClass`, entity class via `$item->entityClass`
- method `getItem()` in `Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry` was renamed to `getDefinition()`
- method `generate()` in `Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider` was replaced by `getRouteItem(string $controllerClass, ActionType $pageType)` - pass the controller class string instead of a `Shopsys\AdministrationBundle\Component\Crud\Definition` object
- constants `IS_CRUD_CONTROLLER`, `CRUD_ACTION`, and `CRUD_ROLE_CONSTANT` have been moved from `Shopsys\AdministrationBundle\Component\Router\CrudControllerRouteLoader` to `Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider`
- property `$menuTitle` in `Shopsys\AdministrationBundle\Component\Config\CrudConfig` is now private - use `setMenuTitle()` method instead of direct property access
- method `getCleanControllerName()` in `Shopsys\AdministrationBundle\Component\Crud\Helper\CrudTransformationHelper` is now private - use `transformToRouteName()` or `transformToRouteUrl()` instead
- route names for CRUD controllers with consecutive capital letters in their name changed (e.g., `ABCController` now generates route name `abc` instead of `a_b_c`) - if you reference such routes by name, update them accordingly

#### fixed Next.js static files not having CORS headers ([#4574](https://github.com/shopsys/shopsys/pull/4574))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c4162bc5d97319db57420c96a86f3c6b1b5f557e) to update your project

#### Ensure that the parameter value cannot be removed if the category seo mix depends on it ([#4579](https://github.com/shopsys/shopsys/pull/4579))

- before applying migration `Version20260424085853`, review whether any existing `ready_category_seo_mixes` rows reference `parameter_values` IDs that no longer exist; update those SEO mixes first, otherwise the migration would fail
- editing a ready category SEO mix moved to a dedicated action at route `admin_categoryseo_readycombination_edit` (path `/seo/category/ready-combination/edit/{id}`); the existing `admin_categoryseo_readycombination` route is now wizard-only (creates a new mix for a combination and redirects to the edit route if one already exists for that combination) so update your project appropriately

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ed33f1d44c9573841a4431ed13597dfa164b1a35) to update your project

#### nginx: avoid storefront fallback for missing content locale overrides ([#4581](https://github.com/shopsys/shopsys/pull/4581))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b52829d535a927a567fe46ee5bcf7aec5a7f71f2) to update your project

#### Gift price is always entered with VAT ([#4583](https://github.com/shopsys/shopsys/pull/4583))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7af19019669da1eb3225f220bf6451e096102967) to update your project

#### Admin sidebar redesign ([#4573](https://github.com/shopsys/shopsys/pull/4573))

- the admin sidebar was visually redesigned (gradient background, white nav links, primary color updated from `#009bd9` to `#2478e0`); no PHP, GraphQL, or configuration changes
- place your own `logo-project.svg` into `app/assets/public/admin/images/logo-project.svg` (a "Your logo" placeholder is provided by project-base) to avoid a broken image; override the Twig blocks if you need different markup
- if your project overrides `layout/layout_with_panel.html.twig`, re-sync it with the new structure (added blocks and the `navbar-platform-brand` wrapper)
- if your project copies or overrides `packages/framework/assets/public/admin/images/logo.svg`, note that the framework asset was replaced with a white/yellow variant designed for the new dark gradient sidebar; update your override accordingly
- if your custom admin SCSS hard-codes the previous primary color `#009bd9` or the `15rem` sidebar width, update it to `#2478e0` and `18rem` (or, preferably, consume `--tblr-primary` and the new `$sidebar-width` variable from `packages/administration/assets/src/styles/sidebar.scss`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/db8956017d8d7d36033e70f26d10b368817d7634) to update your project

#### Fixed GrapesJS video preview in Safari ([#4590](https://github.com/shopsys/shopsys/pull/4590))

- YouTube embeds started showing `Error 153 - Video player configuration error` in the GrapesJS editor preview in Safari. The issue affected only the editor preview; saved content still needs to render a real YouTube iframe on the storefront.
- YouTube videos are now rendered in the editor as a custom thumbnail placeholder with a play icon instead of loading the real YouTube iframe. This means YouTube videos are no longer playable directly inside the editor, and playback should be tested on the storefront after saving the article.
- Existing YouTube and YouTube no-cookie embeds are still parsed as video components when reopening saved content in the editor.
- The deprecated YouTube `modestbranding` option was removed from the editor UI because YouTube no longer supports it and the parameter has no effect.
- Autoplay behavior still depends on browser policies and may be blocked by the browser, especially for videos with sound or before the user interacts with the page.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/05bcbb9f7683fecb996b5dc165a684463b3f50a5) to update your project

#### fix annotation-fixer to always add whitespace after comma in generic types ([#4596](https://github.com/shopsys/shopsys/pull/4596))

- run `php phing annotations-fix` to fix the annotations in your project
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7ff85948a8133f92b0bb5ff4af49d4be302b492a) to update your project

#### Replace duplicated category tree forms with generic TreeSelectionType ([#4587](https://github.com/shopsys/shopsys/pull/4587))

- form types `Shopsys\FrameworkBundle\Form\CategoryCheckboxType` and `Shopsys\FrameworkBundle\Form\BlogCategoryCheckboxType` were removed
    - if you used them, switch to the new `Shopsys\FrameworkBundle\Form\TreeSelectionType` (or its wrappers `Shopsys\FrameworkBundle\Form\CategoriesType` / `Shopsys\FrameworkBundle\Form\BlogCategoriesType`)
- data transformers `Shopsys\FrameworkBundle\Form\Transformers\CategoriesTypeTransformer` and `Shopsys\FrameworkBundle\Form\Transformers\BlogCategoriesTypeTransformer` were removed
    - the new `TreeSelectionType` uses `Shopsys\FrameworkBundle\Form\Transformers\IdsToEntitiesTransformer` internally
- method `getAllCategoriesOfCollapsedTree` in `Shopsys\FrameworkBundle\Model\Category\CategoryFacade` was renamed to `getCollapsedTree`
- method `getAllBlogCategoriesOfCollapsedTree` in `Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade` was renamed to `getCollapsedTree`
- `Shopsys\FrameworkBundle\Controller\Admin\CategoryController::loadBranchJsonAction` and `Shopsys\FrameworkBundle\Controller\Admin\BlogCategoryController::loadBranchJsonAction` route and method signature changed
    - route path order changed from `/{domainId}/{id}` to `/{id}/{domainId}` and `domainId` is now optional
    - method signature changed from `(int $domainId, int $id)` to `(int $id, ?int $domainId = null)`
    - JSON response field `categoryName` was renamed to `label`
- `Shopsys\FrameworkBundle\Form\CategoriesType` and `Shopsys\FrameworkBundle\Form\BlogCategoriesType` no longer extend `CollectionType`, they now extend `Shopsys\FrameworkBundle\Form\TreeSelectionType`
    - the `domain_id` option is no longer required (now optional and nullable)
    - the `entry_type`, `entry_options`, `allow_add`, `allow_delete` and `prototype` options are no longer applicable
- Twig templates `packages/administration/templates/form/type/CategoriesType.html.twig`, `packages/administration/templates/form/type/BlogCategoriesType.html.twig`, `packages/administration/templates/form/type/CategoryCheckboxType.html.twig` and `packages/administration/templates/form/type/BlogCategoryCheckboxType.html.twig` were removed
    - their Twig blocks `categories_widget`, `blog_categories_widget`, `category_checkbox_widget` and `blog_category_checkbox_widget` were replaced by a single `tree_selection_widget` block (with macro `treeSelectionItem`) in `packages/administration/templates/form/type/TreeSelectionType.html.twig`
    - if you customized any of these blocks, port the customization to the new `tree_selection_widget` block
- the admin JS class `CategoryTreeForm` was renamed to `TreeSelectionForm` and `CategoryTreeFormItem` to `TreeSelectionFormItem`
    - the CSS selector class prefix `js-category-tree-form*` was renamed to `js-tree-selection-form*` (`js-category-tree-form`, `js-category-tree-form-item`, `js-category-tree-form-item-icon`, `js-category-tree-form-item-checkbox`, `js-category-tree-form-children-container`)
    - the `data-prototype` attribute was replaced by an inline `<template class="js-tree-selection-form-item-template">` element, and the new attributes `data-checkbox-name` and `data-checkbox-id-prefix` are required on the form root
    - update any custom JS or LESS/CSS selectors referencing the old class names
- new interfaces `Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionEntityInterface` and `Shopsys\FrameworkBundle\Form\TreeSelection\TreeSelectionDataProviderInterface` were introduced
    - they are implemented by `Category`/`BlogCategory` and `CategoryFacade`/`BlogCategoryFacade` respectively
    - if your project facade subclass declared a method named `getAllCategoriesOfCollapsedTree` or `getAllBlogCategoriesOfCollapsedTree`, rename it to `getCollapsedTree` to satisfy the interface
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/57e713d698fe7b3c19ccd0c25b2a700aa3da15d1) to update your project

#### admin: replace query parameters with path variables in selected admin routes ([#4608](https://github.com/shopsys/shopsys/pull/4608))

- route `admin_bestsellingproduct_detail` (`Shopsys\FrameworkBundle\Controller\Admin\BestsellingProductController::detailAction`) changed its URI and method signature
    - URI changed from `/product/bestselling-product/detail/` (with query params `domainId` and `categoryId`) to `/product/bestselling-product/detail/domain/{domainId}/category/{categoryId}/`
    - update all URL generations for this route to pass `domainId` and `categoryId` as path parameters
- route `admin_languageconstant_edit` (`Shopsys\FrameworkBundle\Controller\Admin\LanguageConstantController::editAction`) changed its URI and method signature
    - URI changed from `/constant/edit/` (with query params `key` and `namespace`) to `/constant/edit/{namespace}/{key}/`
    - update all URL generations for this route to pass `key`, and pass `namespace` when you need a non-default namespace
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/918258b12f3c503c287ca2004e098dee91c97224) to update your project

#### replaced ixdotai/smtp with maildev/maildev for better local DX ([#4609](https://github.com/shopsys/shopsys/pull/4609))

- recreate the local SMTP container so the new `maildev/maildev` image is used: `docker compose up -d --force-recreate smtp-server`
- the local SMTP port changed from `25` to `1025`; if your project overrides `MAILER_DSN` in `.env.local` (or any other local env file), update the port accordingly
- to forward outgoing development emails to real recipients (e.g. via Gmail), follow the new ["How do I deliver emails to real recipients from my local environment?"](https://docs.shopsys.com/en/latest/introduction/faq-and-common-issues/#how-do-i-deliver-emails-to-real-recipients-from-my-local-environment) section in the FAQ
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c495ca8afbfd9ed2d099359d2ce829a4ac4221a) to update your project

#### Cache storefront translations across route changes ([#4610](https://github.com/shopsys/shopsys/pull/4610))

- `Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade::cleanStorefrontTranslationCache()` now also bumps the storefront translation version used for client-side translation cache invalidation
    - if your project overrides this method, call the parent implementation or bump the translation version in your override
- `Shopsys\FrameworkBundle\Model\LanguageConstant\LanguageConstant::getTranslation($locale)` no longer creates a missing locale translation entity when an explicit locale is passed
    - if your project relies on this side effect, use `editTranslation()` or `translation()` in your custom entity logic instead
- user translation override JSON files are now generated before the storefront translation cache version is bumped
    - if your project overrides language constant saving/deleting logic, keep this order so the storefront does not refresh translations before `/content/locales/{locale}/{namespace}.json` is updated

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a6c885ac1ef239d17f3bf0abad19401edda91718) to update your project

#### admin: move administrator activity updates out of the user provider ([#4613](https://github.com/shopsys/shopsys/pull/4613))

- `Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivityFacade::updateCurrentActivityLastActionTime()` was renamed to `updateCurrentActivity()`
    - the method now updates both the administrator's `lastActivity` and the current administrator activity's last action time in a single flush
    - update your custom calls or overrides to use `updateCurrentActivity()` instead

#### Remove ExtendedApiClassNamespaceSniffer and FrontendApiNamespaceSniffer ([#4604](https://github.com/shopsys/shopsys/pull/4604))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/30156c591ba2dd6109efb7b9651144f2478f6594) to update your project

<!-- backendNotes -->

### Storefront

#### Added image support for color parameter values ([#4325](https://github.com/shopsys/shopsys/pull/4325))

- color parameter filter now displays uploaded images instead of color circles when available
- `ColorLabelWrapper` component now accepts `imageUrl` and `imageName` props to render uploaded images
- `CheckboxColor` component now passes image props to `ColorLabelWrapper`
- changed `ColorLabelWrapper` to use the project's custom `Image` component instead of `next/image` directly to ensure proper loader configuration for external URLs
- updated `ProductFilterOptionsParametersColorFragment.graphql` to include `colourIconImage { url name }` field
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5a69a806782fba6c1d5bbb855df7936b09596d28) to update your project

#### Replace Select with RadiobuttonGroup for customer user role selection ([#4349](https://github.com/shopsys/shopsys/pull/4349))

- role selection in `ManageCustomerUserPopup` now uses `RadiobuttonGroup` component instead of dropdown select for better UI & UX
- `CustomerUserManageProfileFormType.roleGroup` type changed from `SelectOptionType` to `string` (UUID)
- `validateRoleGroup` validation simplified to validate a string instead of an object
- new `useCustomerUserGroupsAsRadiobuttonOptions` hook provides role options for radio buttons
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/397edc911ca6ed65ac8f41cab5cbb943f1d6ca75) to update your project

#### Button loading indicator + polishing ([#4352](https://github.com/shopsys/shopsys/pull/4352))

- fixed the white flash during navigation from Cart to Transport and Payment by moving the animate-in animation to SkeletonManager
- added a banner image fallback to ensure valid HTML
- introduced a default loading indicator for form submit buttons
- polished and unified the design of the wishlist and comparison pages
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7a2e1ce5899e94e76c540ccf0ba8dae3dace61e1) to update your project

#### Remove Shopsys naming ([#4354](https://github.com/shopsys/shopsys/pull/4354))

- the codebase is now free of Shopsys-specific naming
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/48bf29d014286ff0fcb704e315070b6c21c0657a) to update your project

#### enhance debug error messages & option to ignore them by type ([#4356](https://github.com/shopsys/shopsys/pull/4356))

- improved UI of debug flash messages
- short description of the error & option to show detailed error log (formatted)
- copy formatted error log
- option to ignore errors (silence error messages) by error types (store in local storage)
- added debug error messages manager to `/styleguide` **only in development** environment for visual management of ignored errors (view/clear/reset all)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/165c083866f141f5850a5872dd19421f0786dd12) to update your project

#### Refactoring ([#4358](https://github.com/shopsys/shopsys/pull/4358))

- enabled `noUnusedLocals` and `noUnusedParameters` in tsconfig
- added `@ts-nocheck` to generated GraphQL files
- removed unused exports, imports, components, utils, hooks, types, and function parameters
- removed unused dependencies
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6bddbd9b9ea66d7c7d4f826d3b6a2020b13910e7) to update your project
- see also #project-base-diff of [#4387](https://github.com/shopsys/shopsys/pull/4387) with an additional fix

#### remove `data-tid` attributes from production build ([#4359](https://github.com/shopsys/shopsys/pull/4359))

- `data-tid` attributes are now automatically removed from production storefront builds for cleaner HTML output
- if you run Cypress or E2E tests against production builds, ensure `CYPRESS_KEEP_TID=1` environment variable is set (already configured in provided docker-compose files)
- if you have custom testing tools or scripts that rely on `data-tid` attributes in production, either:
    - set `CYPRESS_KEEP_TID=1` during build to preserve the attributes
    - update your selectors to use alternative attributes
- Cypress Docker image upgraded from `13.14.2-node-v18.16.0-chrome-126` to `13.17.0-node-v20.18.0-chrome-131`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/31f486b163a84f426184836396413f2202efe875) to update your project

#### complaint cannot be created for orders created as non-logged customer ([#4369](https://github.com/shopsys/shopsys/pull/4369))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/35a667292f22a35c22fc88e342f48026b7bd11b4) to update your project

#### Remove crypto-js and use native Web Crypto API ([#4374](https://github.com/shopsys/shopsys/pull/4374))

- removed `crypto-js` dependency to reduce bundle size
- refactored `sha256` utility to use native Web Crypto API (`crypto.subtle.digest`)
- added Sentry logging to `sha256` function to monitor Web Crypto API failures
- improved safety in `fetcher.ts` by moving dynamic crypto import inside function
- GTM events will still fire on old browsers (<1% of users: IE11, Safari <15.4), but without `emailHash` (enhanced conversions not available)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/58a1c1dadf06759ad0e1b6022d9126b1bf43364a) to update your project

#### SEO Improvements ([#4379](https://github.com/shopsys/shopsys/pull/4379))

- added `ArticleMetadata` component for Article structured data (JSON-LD) on article and blog pages
- added `x-default` hreflang fallback for non-EN/CS/SK users in `SeoMeta.tsx`
- fixed invalid Open Graph attribute `name` to `property`
- fixed canonical URL to always render with fallback to current URL in `SeoMeta.tsx`
- reorganized meta tags in `SeoMeta.tsx` into logical order (title → description → canonical → hreflang → Open Graph → Twitter)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/94f0694e8b0b966e8df224eb71ef9e2c9e5f6030) to update your project

#### Catalog page ([#4378](https://github.com/shopsys/shopsys/pull/4378))

- a new `/catalog` page (`pages/catalog.tsx`) is introduced, rendering L1 categories sourced from the catalog navigation item via the new `CatalogContent` component
    - if you have a custom navigation structure, ensure the navigation item that should back the catalog page is recognized as `SkeletonEnum.Catalog` (either by `routeName` or by link) so categories render correctly
- a new `CategoryCard` component was extracted and is now shared between `PromotedCategoriesContent` and `CatalogContent`
    - if you have overridden `PromotedCategoriesContent`, update it to use the new `CategoryCard` component to avoid divergence
- a new `catalog` entry was added to `FriendlyPagesTypes` and `FriendlyPagesDestinations` (`types/friendlyUrl.ts`), together with `SkeletonEnum.Catalog`, `GtmPageType.catalog` and the `/catalog` route
    - extend your own enums/route config accordingly if they shadow the built-ins
- a new `Catalog` translation key was added to `public/locales/{cs,sk,en}/common.json`
    - add the translation to your project locale files
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/fd20ed5907ed73e892e82ab1a99fca9e8661dd74) to update your project

#### Fix product detail image ([#4380](https://github.com/shopsys/shopsys/pull/4380))

- fixed product detail image height
- migrate all Tailwind !important modifiers from legacy prefix syntax (!utility) to Tailwind v4 suffix syntax (utility!)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d8a173d67f455ee9975a0904ecd9c3b80805c673) to update your project

#### Fix keys for React lists & add missing api unique identifiers ([#4368](https://github.com/shopsys/shopsys/pull/4368))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a685c0cdb4c78c74e9d3c926b67c4e98da6779d1) to update your project

#### SF product detail - add parameter value color preview ([#4382](https://github.com/shopsys/shopsys/pull/4382))

- parameters sorting was removed from storefront ProductDetailContent as they are already sorted from API
- created new `ColorPreview` component that is used for rendering parameter values icon/color preview on product detail, in product filter option checkbox, and in filter selected parameters
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2446629c05091ce1ade74a8744653383b04b48ee) to update your project

#### Remove product detail tabs ([#4384](https://github.com/shopsys/shopsys/pull/4384))

- replaced product detail tabs with a custom scroll-based solution
- added footer placeholders for improved UX
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8c4864cf3cab8c0d2af24136d5ed3e0f9d09633b) to update your project

#### Error handling refactoring ([#4386](https://github.com/shopsys/shopsys/pull/4386))

This PR introduces a centralized error handling system with `ErrorOrchestrator` and `useErrorHandler` hook, replacing scattered error handling patterns across the storefront.

##### New Error Handling Architecture

The new system uses:

- **`ErrorOrchestrator`** (`utils/errors/ErrorOrchestrator.ts`) - Central decision engine for error handling
- **`useErrorHandler`** hook (`utils/errors/useErrorHandler.ts`) - React hook for component-level error handling

##### Migration from `handleFormErrors` to `useErrorHandler`

If you have custom forms using `handleFormErrors`, migrate to the new `useErrorHandler` hook:

**Before:**

```typescript
import { handleFormErrors } from 'utils/forms/handleFormErrors';

const onSubmit = async (data: FormData) => {
    const result = await mutation({ input: data });
    handleFormErrors(result.error, formProviderMethods, t, formMeta.messages.error);
};
```

**After:**

```typescript
import { useErrorHandler } from 'utils/errors/useErrorHandler';

const handleError = useErrorHandler({
    form: formProviderMethods,
    customMessage: formMeta.messages.error,
});

const onSubmit = async (data: FormData) => {
    const result = await mutation({ input: data });
    handleError(result.error);
};
```

##### useErrorHandler Hook Options

The hook accepts the following options:

| Option           | Type                                                 | Description                            |
| ---------------- | ---------------------------------------------------- | -------------------------------------- |
| `form`           | `UseFormReturn<TFormValues>`                         | Form instance for setting field errors |
| `gtmOrigin`      | `GtmMessageOriginType`                               | GTM tracking origin (default: `other`) |
| `customMessage`  | `string`                                             | Override error message for toasts      |
| `customHandlers` | `Partial<Record<ApplicationErrorsType, () => void>>` | Custom handlers for specific errors    |

##### useErrorHandler Dependency Optimization

The `useErrorHandler` hook uses optimized dependencies to prevent unnecessary re-renders:

```typescript
// The hook uses these specific dependencies instead of the entire options object
[t, form, gtmOrigin, customMessage, customHandlers];
```

If you pass inline objects to the hook, consider memoizing `customHandlers` for optimal performance:

```typescript
// Suboptimal - customHandlers recreated each render
const handleError = useErrorHandler({
    form: formProviderMethods,
    customHandlers: {
        'some-error': () => doSomething(),
    },
});

// Better - memoize customHandlers
const customHandlers = useMemo(
    () => ({
        'some-error': () => doSomething(),
    }),
    [doSomething],
);

const handleError = useErrorHandler({
    form: formProviderMethods,
    customHandlers,
});
```

##### Type Safety Improvements for ValidationErrors

The `ValidationErrors` type in `types/error.ts` now correctly allows undefined values for field lookups:

**Before:**

```typescript
export type ValidationErrors = {
    [fieldName: string]: {
        message: string;
        code: string;
    };
};
```

**After:**

```typescript
export type ValidationErrors = Partial<
    Record<
        string,
        {
            message: string;
            code: string;
        }
    >
>;
```

If you access validation errors by field name, you must now handle the potentially undefined value:

```typescript
// Before - would compile but could fail at runtime
const error = validationErrors[fieldName];
showErrorMessage(error.message);

// After - must check for undefined
const error = validationErrors[fieldName];
if (error) {
    showErrorMessage(error.message);
}
```

##### New Exports from parseGraphqlError.ts

The following functions and types are now exported from `utils/errors/parseGraphqlError.ts`:

- `parseGraphqlErrorExtensions` - Parse extensions from GraphQL errors
- `flattenValidationErrors` - Flatten validation errors into array format
- `ParsedGraphqlError` type
- `RawValidationErrors` type

##### Error Code Verbosity Changes

Many error codes have changed verbosity levels. If you rely on specific error behavior, review the changes in `applicationErrors.ts`:

**Moved from `flash-message` to `no-flash-message`:**

- `blog-category-not-found`
- `article-not-found`

**Moved from `flash-message` to `no-log`:**

- Wishlist/comparison "already in list" and "not in list" errors are now silent (expected behavior)

**Error code naming convention changed:**

- `comparison-*` → `COMPARISON-*` (uppercase prefix)
- `wishlist-*` → `WISHLIST-*` (uppercase prefix)

If you have custom error handling that checks for these codes, update the string literals.

##### Custom ErrorBoundary

The `react-error-boundary` package has been removed. If you imported from it, use the custom implementation:

**Before:**

```typescript
import { ErrorBoundary } from 'react-error-boundary';
```

**After:**

```typescript
import { ErrorBoundary } from 'components/Basic/ErrorBoundary/ErrorBoundary';
```

##### Toast Deduplication

Toasts are now deduplicated by `{errorType}:{fieldName}` instead of message text. If you have custom toast logic that relies on message-based deduplication, update accordingly.

##### Centralized Mutation Error Handling in errorExchange

Mutation errors for cart operations are now handled centrally in `errorExchange.ts` via `MUTATION_ERROR_CONFIG`. If you have custom mutations that were relying on hook-level error handling, you may need to add them to this config.

The `MUTATION_ERROR_CONFIG` is typed as `Partial<Record<string, MutationErrorConfig>>` to properly indicate that not all mutation names have configurations.

**Mutations with centralized error handling:**

| Mutation                          | Error Type                | Validation Fields                    |
| --------------------------------- | ------------------------- | ------------------------------------ |
| `AddToCartMutation`               | `add-to-cart-error`       | -                                    |
| `AddOrderItemsToCartMutation`     | `add-order-items-error`   | -                                    |
| `ApplyPromoCodeToCartMutation`    | `promo-code-apply-error`  | `promoCode`                          |
| `RemovePromoCodeFromCartMutation` | `promo-code-remove-error` | `promoCode`                          |
| `ChangePaymentInCartMutation`     | `payment-error`           | `payment`, `goPaySwift`              |
| `ChangeTransportInCartMutation`   | `transport-error`         | `transport`, `pickupPlaceIdentifier` |

**Hook changes:**

The following hooks no longer handle their own error toasts - they just return `null` on error:

- `useApplyPromoCodeToCart` - removed `error` message parameter
- `useRemovePromoCodeFromCart` - removed `error` message parameter
- `useChangePaymentInCart` - errors handled by errorExchange
- `useChangeTransportInCart` - errors handled by errorExchange
- `useAddToCart` - errors handled by errorExchange
- `useAddOrderItemsToCart` - errors handled by errorExchange

If you have custom code that passes error messages to these hooks, remove those parameters.

##### Component Changes

- `OrderWithdrawalContent` now uses only `useErrorPopup` for form validation errors, consistent with other form components. If you extended this component and relied on the `useErrorHandler` hook, use `useErrorPopup` instead.

##### Package Updates

Major dependency updates that may affect your code:

- `next`: 14.1.4 → 15.4.10
- `@sentry/nextjs`: 7.99.0 → 9.38.0
- `vitest`: 0.34.6 → 2.1.9

If you have custom Sentry configuration or vitest tests, review compatibility with the new versions.

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2e85e922a2bb4749d655c7cc98639f08cb483164) to update your project

#### Add graceful degradation for unsupported browsers ([#4390](https://github.com/shopsys/shopsys/pull/4390))

A dismissible warning banner is now shown for users on unsupported browsers (iOS 12, Safari 12, and other browsers that don't support ES2020 features like optional chaining and nullish coalescing).

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a8a70436eeb86c83006162198bee2f7c0e7fccca) to update your project

If you have customized `pages/_document.tsx`, add the browser warning functionality:

1. Add the `BROWSER_WARNING_SCRIPT` and `BROWSER_WARNING_STYLES` constants before the `MyDocument` class
2. Include the warning styles and script in the `<Head>` component
3. Add the warning banner HTML inside `<body>` before `<Main />`

See the complete implementation in `project-base/storefront/pages/_document.tsx`.

#### Add knip ([#4393](https://github.com/shopsys/shopsys/pull/4393))

- added [Knip](https://knip.dev/) for unused code detection in the storefront
- Knip runs in CI pipelines (GitLab CI and GitHub Actions) and will fail if unused files, exports, or dependencies are detected
- run `pnpm run knip` locally to check for unused code before pushing
- added `@graphql-codegen/typescript` and `@graphql-codegen/add` as explicit dependencies (previously used implicitly)
- removed unused code found by Knip: unused GraphQL queries/fragments, Cypress helper functions, test configuration files, and unnecessary exports
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e2412a83c8e5cf997b0daef9a6fbda06bfb46afd) to update your project

#### Update to React v19 ([#4404](https://github.com/shopsys/shopsys/pull/4404))

##### Required Dependency Updates

```json
{
    "react": "~19.2.3",
    "react-dom": "~19.2.3",
    "@types/react": "~19.2.3",
    "@types/react-dom": "~19.2.3",
    "urql": "~5.0.1",
    "@urql/exchange-graphcache": "~7.2.0",
    "react-hook-form": "~7.54.0",
    "@testing-library/react": "~16.2.0",
    "@testing-library/dom": "^10.4.1"
}
```

##### Breaking Changes - React 19

- **JSX.Element → ReactElement**: `JSX.Element` type is deprecated - replace with `ReactElement` from 'react'
- **RefObject Type**: `RefObject<T>` must include `null` - change to `RefObject<T | null>`
- **useRef Initial Value**: `useRef<T>()` without argument no longer compiles - use `useRef<T>(undefined)` for proper typing
- **urql Cache Reads**: Cache reads during render cause warnings in React 19 - move `readFromCache` calls from `useState` initialization to `useEffect`

##### Breaking Changes - react-hook-form 7.54

`DeepPartial<T>` type is no longer compatible with `useForm` defaultValues parameter. Update type imports:

```diff
- import { DeepPartial, FieldValues, UseFormReturn } from 'react-hook-form';
+ import { DefaultValues, FieldValues, UseFormReturn } from 'react-hook-form';
```

##### Breaking Changes - @testing-library/react 16

Manual `cleanup()` calls are no longer needed. Remove from test files:

```diff
- import { cleanup, render, screen } from '@testing-library/react';
+ import { render, screen } from '@testing-library/react';

- afterEach(() => {
-     cleanup();
- });
```

##### urql 5.x Upgrade

urql 5.x is required because React 19 is stricter about state updates during render. urql 4.x triggers "Cannot update a component while rendering" warnings due to cache synchronization.

**SSR compatibility**: Our setup uses `initUrqlClient` + manual `Provider` (not `withUrqlClient` HOC which relies on `react-ssr-prepass`), so there are no React 19 SSR compatibility issues.

##### React Compiler

Enable in `next.config.js`:

```javascript
experimental: {
    reactCompiler: true,
}
```

React Compiler automatically adds memoization at build time. You can optionally remove most manual `useMemo`, `useCallback`, and `React.memo` calls - they're now redundant.

##### ESLint Warnings (react-hooks/exhaustive-deps)

The codebase has ~68 `react-hooks/exhaustive-deps` warnings. **These are expected and the app runs correctly.**

**Future plan**: Refactor using React 19.2's `useEffectEvent` hook which properly handles these cases. Some warnings may remain with `eslint-disable` comments where intentional.

##### Peer Dependency Warnings

These warnings are safe to ignore - packages are compatible despite outdated peer dependency metadata:

- `next-urql` expecting `urql@^4.0.0`
- `zustand` expecting `use-sync-external-store`

##### References

- [React 19 Upgrade Guide](https://react.dev/blog/2024/04/25/react-19-upgrade-guide) - Official upgrade guide with breaking changes and codemods
- [React v19 Release](https://react.dev/blog/2024/12/05/react-19) - Release announcement
- [React Compiler](https://react.dev/learn/react-compiler) - Documentation for React Compiler
- [React 19.2](https://react.dev/blog/2025/10/01/react-19-2) - Release notes including `useEffectEvent` hook

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/65c418f47b3ad0828a117359f43d0b80ccac0c87) to update your project

#### Product detail tab on SK domain ([#4410](https://github.com/shopsys/shopsys/pull/4410))

- tabs on SK domain work correctly now
- fixed design for parameters on responsive
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7379470fb163272478d7e300da34cb2fe9e60495) to update your project

#### Complaint button ([#4436](https://github.com/shopsys/shopsys/pull/4436))

- updated the condition for displaying the complaint button when an order is withdrawn
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d972ac9b1af1056467b71daceb3002825f81217f) to update your project

#### GoPay payment status notify endpoint is now handled by backend instead of storefront ([#4439](https://github.com/shopsys/shopsys/pull/4439))

- the `/order/payment-status-notify` route has been removed from the storefront as the endpoint is now handled by the backend
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/c28b434bcd00e56e71d3de1c780bb9fac205341f) to update your project

#### security headers ([#4447](https://github.com/shopsys/shopsys/pull/4447))

- be sure to fetch `cspHeader` from the `Settings` query and set the `Content-Security-Policy` header properly in `initServerSideProps.ts`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9dad7a39d3ca1b377efa47b08ca276c722dd89ad) to update your project

#### enable launching application without Sentry and CDN ([#4458](https://github.com/shopsys/shopsys/pull/4458))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7297c9b4d943cc60383eef2ac42c950d2fee2e4b) to update your project

#### Refactor after update React to v19 ([#4434](https://github.com/shopsys/shopsys/pull/4434))

- updated storefront dependencies: React 19.2.4, Next.js 15.5.12, TypeScript 5.9.3, `eslint-plugin-react-hooks` 7.x, `@types/react` 19.2.13
- updated ESLint config to use `reactHooks.configs.flat.recommended` (new flat config API from `eslint-plugin-react-hooks` 7.x) instead of manually registering the `react-hooks` plugin and its rules
- migrated Tailwind CSS classes to v4 syntax:
    - `!utility` → `utility!` (e.g., `!block` → `block!`, `!no-underline` → `no-underline!`)
    - `bg-gradient-to-r` / `bg-gradient-to-l` → `bg-linear-to-r` / `bg-linear-to-l`
    - `break-words` → `wrap-break-word`
    - `outline-none` → `outline-hidden` (v4 changed `outline-none` to mean `outline-style: none`; the old behavior is now `outline-hidden`)
    - `outline-offset-[-2px]` → `-outline-offset-2`, `[0px]` → `0`
    - `max-h-[144px]` → `max-h-36`
    - removed standalone `transform` class (auto-applied in v4 when using transform utilities like `rotate-*`, `translate-*`)
    - `flex-shrink-0` → `shrink-0`
- introduced `useEffectEvent` (React 19.2) across hooks and components to wrap mutable callbacks referenced inside `useEffect`, preventing stale closures without adding unstable references to dependency arrays
- fixed `useEffect` exhaustive dependency arrays across the storefront — all effects now list their true dependencies, reducing bugs from stale closures
- wrapped non-urgent state updates (window resize, scroll position, timers, debounced values) in `startTransition` to keep the UI responsive during concurrent rendering
- replaced `createRef` with `useRef` in `ModalGallery` to avoid creating a new ref object on every render
- replaced `useEffect`-based prop-to-state sync with the "previous value" pattern (comparing props during render) in `StoreListItem` to avoid unnecessary extra renders
- initialized `useState` with prop values directly instead of syncing via `useEffect` where applicable (e.g., `Spinbox`)
- added no-JS fallback for pages using React streaming SSR with Tailwind v4 — Tailwind v4 preflight sets `[hidden] { display: none !important }` which hides React's streamed `<div hidden id="S:...">` content permanently when JavaScript is disabled; a `@media (scripting: none)` override in `base.css` restores visibility by showing the hidden streaming div and collapsing the empty `#__next` shell on Suspense pages
    - https://github.com/tailwindlabs/tailwindcss.com/pull/2321
    - https://github.com/tailwindlabs/tailwindcss/issues/18653
- added SSR Cypress tests to verify that server-rendered pages display correct content without client-side JavaScript
- extracted shared Cypress utilities for GraphQL queries and entity data fetching into reusable helpers
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7319c631bb4121204d9d55a0d0cf537569cd38cb) to update your project

#### Performance refactor ([#4460](https://github.com/shopsys/shopsys/pull/4460))

- `useDeferredRender` hook (`utils/useDeferredRender.ts`) now uses wave-based timing with explicit `[delayMs, places[]]` tuples per page instead of sequential index-based deferral with `DEFER_START`/`DEFER_GAP` — if you have custom deferred components, update their place names to match the new wave configuration
- `Loaders` component (`components/Pages/App/Loaders.tsx`) has been split into `Loaders`, `SecondaryLoaders`, and `TertiaryLoaders` for better code splitting — if you added custom loaders to `Loaders.tsx`, move them to the appropriate tier based on priority
- new `CurrentCustomerUserProvider` (`components/providers/CurrentCustomerUserProvider.tsx`) centralizes the customer user query into a single context — if you use `useCurrentCustomerUserQuery` directly, switch to `useCurrentCustomerUserQueryData` from this provider
- `MenuIconic` now uses a count-only `useProductListCount` hook instead of fetching full product data via `useComparison`/`useWishlist`; new `itemsCount` field has been added to the `ProductList` GraphQL type
- `BannersSlider` defers auto-rotation until user interaction (scroll/pointer down) for better LCP; only the first slide is rendered initially
- several components (`ProductsSlider`, `ProductDetailAccessories`, `CreateComplaintPopup`, `StoresWrapper`, `StoreDetailContent`, `SelectListInfiniteScroll`) have been switched to dynamic imports for better code splitting
- `SkeletonManager` skips the fade-in animation on initial SSR load; `useScrollTop` uses `IntersectionObserver` instead of scroll events; `useHashNavigation` uses `requestAnimationFrame` throttling; `BannerImage` first banner now has `fetchPriority="high"` and `decoding="auto"`
- `384` has been added to `imageSizes` in `next.config.js` to align with the backend image resizer
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0ad7b89eab24981773766b32bed1e21991013a2f) to update your project

#### Fix autocomplete popup after submit search form ([#4467](https://github.com/shopsys/shopsys/pull/4467))

- autocomplete search popup no longer flashes on submit
- search page now shows correct skeleton
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e964f31d50fc35b1974538f25b4c15832cc4b061) to update your project

#### Improved order weight alert handling ([#4468](https://github.com/shopsys/shopsys/pull/4468))

- transport validation errors from last order restore are now silently suppressed instead of shown as error toast
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f289c69d4c7671ff34aecd41c3972a2a2a0bbbfd) to update your project

#### Lazy-load Packeta widget and add country filtering ([#4476](https://github.com/shopsys/shopsys/pull/4476))

- the Packeta widget script is no longer loaded globally via `<Script>` on the transport-and-payment page; it is now loaded on-demand when the user opens the Packeta pickup place popup
- added `packeteryCountry` property to domain configuration (`DomainConfigType` in `domainConfig.ts`, `NextConfigPublicRuntimeConfig` in `getNextConfig.ts`, and `next.config.js`) to filter Packeta pickup points by country; supports multiple countries as a comma-separated string (e.g. `"cz,sk"`)
- changed `Window.Packeta` type from required to optional in `globals.d.ts` (the widget script is no longer guaranteed to be loaded)
- added error handling with a toast message when the Packeta script fails to load
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2153bbd767b1906ffea94b913298ef2a930cd7d4) to update your project

#### Empty Luigis box ([#4477](https://github.com/shopsys/shopsys/pull/4477))

- LuigisBox sets totalCount to -1 for non-main types, which is truthy in JavaScript, causing the "no results" check to always evaluate as "has results"
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5b8b6b63c12378f26f0199002bea85d0626ac23f) to update your project

#### Fix transport and payment race conditions ([#4480](https://github.com/shopsys/shopsys/pull/4480))

- transport and payment radio buttons and reset buttons are now disabled while a transport or payment mutation is in flight
- this prevents a race condition where selecting a new transport during an ongoing payment reset could result in an incompatible transport/payment combination error
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/ec0a6d37df25ee9d593bd57a6c8d025c6ac7b7b8) to update your project

#### Update storefront UI and Cypress support ([#4492](https://github.com/shopsys/shopsys/pull/4492))

- `data-tid` attribute for `TIDs.blocks_promocode_promocodeinfo_code` in `CartPreview.tsx` was moved from the promo code remove button to the promo code info wrapper — if you use this TID to target the remove button, update to use the new `TIDs.blocks_promocode_remove_button` instead
- many storefront components now have new `data-tid` attributes for Cypress test selectors — these are purely additive and do not change component behavior
- the `TIDs` enum in `cypress/tids.ts` has been reorganized with section comments and ~50 new values added (no values removed)
- Cypress test infrastructure now includes B2B support (`loginB2b`, `visitB2bAndWaitForStableAndInteractiveDOM`, `createB2bOrderForTest`), new SNAPSHOT_GROUPs, and improved blackout handling
- `TEST_LOCALE`, `B2B_DOMAIN_ID`, and `B2B_BASE_URL` environment variables are now passed to all Cypress make targets — B2B tests are opt-in and only run when `B2B_DOMAIN_ID` and `B2B_BASE_URL` are set
- CI test matrix expanded with 9 new test groups
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5e1a382825022d3fb864bee079ac67ce3eebbb64) to update your project

#### Storefront config now uses window.\_\_ENV instead of Next.js publicRuntimeConfig ([#4493](https://github.com/shopsys/shopsys/pull/4493))

- **`getNextConfig()` function from `utils/config/getNextConfig` was removed** -- use `getPublicConfig()` from `envConfig` for public config, or `getServerConfigProperty()` from `envConfig` for server config
- **`getPublicConfigProperty()` moved from `utils/config/getNextConfig` to `envConfig`** and no longer accepts a `defaultValue` second parameter -- all config values are now guaranteed to be defined, so drop the default value argument from all call sites:

    ```diff
    - import { getPublicConfigProperty } from 'utils/config/getNextConfig';
    + import { getPublicConfigProperty } from 'envConfig';

    - const apiKey = getPublicConfigProperty('googleMapApiKey', '');
    + const apiKey = getPublicConfigProperty('googleMapApiKey');
    ```

- **`getServerConfigProperty()` moved from `utils/config/getNextConfig` to `envConfig`** -- update the import path:
    ```diff
    - import { getServerConfigProperty } from 'utils/config/getNextConfig';
    + import { getServerConfigProperty } from 'envConfig';
    ```
- **`publicRuntimeConfig` and `serverRuntimeConfig` blocks removed from `next.config.js`** -- config is now built by `buildPublicEnvConfig.ts` and injected via `window.__ENV` in `_document.tsx`; if you added custom properties to `publicRuntimeConfig`, add them to the `PublicRuntimeConfig` type in `envConfig.ts` and populate them in `buildPublicEnvConfig.ts`
- if your tests mock `next/config` via `vi.mock('next/config', ...)`, replace with direct `window.__ENV` assignment or use the new `vitest/helpers/mockPublicConfig.ts` helper
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/14db5037f632d76ea41029985b82397e9770a854) to update your project

#### product detail parameters aligment ([#4496](https://github.com/shopsys/shopsys/pull/4496))

- see #project-base-diff

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/6c505c7a722ebe756619e4874cf03d535f1038d1) to update your project

#### useAddOrderItemsToCart: cart skeleton now shows during repeat order redirect ([#4498](https://github.com/shopsys/shopsys/pull/4498))

- see #project-base-diff

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/096f850a81b51cc3f552ac9cef9e91ae1b5d4f2f) to update your project

#### Forms refactor ([#4503](https://github.com/shopsys/shopsys/pull/4503))

- `handleFormErrors()` utility (`utils/forms/handleFormErrors.ts`) has been removed, use `useErrorHandler` hook instead
- all `use*FormMeta()` hooks no longer accept `formProviderMethods` parameter — remove the argument from all call sites:
    ```diff
    - const formMeta = useLoginFormMeta(formProviderMethods);
    + const formMeta = useLoginFormMeta();
    ```
- `useCustomerUserManageProfileFormMeta` signature changed from `(formProviderMethods, mode)` to `(mode)` — remove the first argument
- per-form meta type definitions (e.g., `LoginFormMetaType`, `ContactFormMetaType`) have been replaced by the shared generic `FormMeta<TForm, TMessages, TExtra>` from `types/formMeta` — update any custom form meta types to use `FormMeta` instead
- `errorMessage` property has been removed from form field meta objects — if you accessed `formMeta.fields.*.errorMessage`, read errors from `formProviderMethods.formState.errors` directly instead
- `useFormWrapper` validation mode changed from `'all'` to `'onTouched'` — fields are now validated only after being touched
- `Form` component now requires a `formName` prop:
    ```diff
    - <Form onSubmit={...}>
    + <Form formName="my-form" onSubmit={...}>
    ```
- `TextInputControlled`, `PasswordInputControlled`, `TextareaControlled`, and `CheckboxControlled` `render` prop is now optional — when omitted, a default `FormLine` wrapper is used
- `CheckboxControlled` `render` callback signature changed from `(input, currentValue)` to `(input)` — remove the second parameter from your render callbacks
- `useRegistrationFormMeta` message key `successAndLogged` renamed to `success`
- new files added: `types/formMeta.ts` (shared `FormMeta` type) and `utils/forms/createFields.ts` (helper to build field meta objects concisely)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a319b27379abab0e33ca2270befb384210159be9) to update your project

#### Remove order transport and payment relations ([#4473](https://github.com/shopsys/shopsys/pull/4473))

- **GraphQL fields `Order#transport` and `Order#payment` have been removed**
    - transport and payment are now available on `OrderItem#transport` and `OrderItem#payment` (nullable)
    - update all GraphQL queries/fragments that fetch `order { transport { ... } }` or `order { payment { ... } }` to instead fetch from the order items:
    ```diff
    fragment on Order {
    -  transport { name }
    -  payment { name }
      items {
        type
    +   transport { name }
    +   payment { name }
      }
    }
    ```
- use new helper functions from `utils/mappers/order` to extract transport/payment items:
    - `getOrderTransportItem(order.items)` returns the transport order item
    - `getOrderPaymentItem(order.items)` returns the payment order item
    - `getOrderRoundingItem(order.items)` returns the rounding order item
- all storefront components that previously accessed `order.transport` or `order.payment` must be updated to use the order item pattern (e.g., `getOrderTransportItem(order.items)?.transport?.name`)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7f458f596cd82ec6704b1afb67a9c9b7812eb83c) to update your project

#### finalize bumps and stabilize Docker/runtime ([#4481](https://github.com/shopsys/shopsys/pull/4481))

- updated storefront and Cypress Dockerfiles to `node:24.14.0-alpine3.22` and Corepack-managed `pnpm` `10.30.3`
- updated storefront dependencies `@next/bundle-analyzer` to `15.5.9`, `@sentry/nextjs` to `10.40.0`, `@mswjs/interceptors` to `0.41.3`, and Cypress dependencies `glob` to `12.x` and `inquirer` to `13.x`
- if you override storefront Dockerfiles, keep the new Corepack bootstrap (`corepack enable && corepack prepare pnpm@10.30.3 --activate`) and install `icu-data-full` and `libc6-compat`
- if you override `project-base/storefront/docker/entrypoint.sh`, keep the retry logic for `pnpm install` in `dev` and `build` modes
- if you override production storefront Docker image setup, keep application files owned by `root:root` and read-only while the runtime still runs as `node`
- if you use custom `SymfonyDebugToolbar` request interception in your storefront, update the `@mswjs/interceptors` integration to the current API
    - replace `@mswjs/interceptors/lib/presets/browser` with explicit `FetchInterceptor` and `XMLHttpRequestInterceptor`
    - `BatchInterceptor` `response` handler now receives an event object, use its `response` property instead of expecting `Response` directly
- if you override `project-base/storefront/next.config.js`, update the Sentry wrapper so the build can run with `SENTRY_BUILD_PLUGIN_DISABLED=1` even when `@sentry/nextjs` build-time integration is disabled
- if you override `project-base/storefront/utils/domain/domainConfig.ts`, keep the fallback to `DEFAULT_LOCALE` when `context.locale` is missing
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f4b8faa397ec0d3ef5f9920ad2fd2349135477da) to update your project

#### Add to cart race condition ([#4501](https://github.com/shopsys/shopsys/pull/4501))

- add-to-cart and remove-from-cart buttons are now properly disabled during mutation requests to prevent duplicate submissions
- product list (wishlist/comparison) mutations now guard against race conditions from concurrent requests using per-product tracking
- replaced BroadcastChannel-based cross-tab sync with a simpler `useRefetchOnTabFocus` hook for cart, wishlist, and comparison data
- urql cache updaters now also update `ProductListCountQuery` to instantly reflect header counts after mutations
- `ProductListCountQuery` GraphQL query now includes `uuid` field for proper graphcache normalization
- refactored `useReloadCart` to use direct urql client query instead of a second `useCartQuery` hook, fixing cart data briefly reverting in other tabs
- removed `useRefetchComparedProducts` and `useRefetchWishedProducts` in favor of the unified `useProductList` hook
- added Cypress tests for wishlist and comparison race conditions
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7b9998a0d9033160c66ac2d101d2f6fef1b18e1b) to update your project

#### Improve work with phone numbers ([#4514](https://github.com/shopsys/shopsys/pull/4514))

- phone number inputs across storefront forms now use a structured format with separate prefix, country code, and number fields instead of a single `telephone` string
    - all form types (`CustomerChangeProfileFormType`, `CustomerUserManageProfileFormType`, `RegistrationFormType`, `OrderWithdrawalFormType`, `DeliveryAddressFormType`, `ComplaintFormType`, `InquiryFormType`, `ContactInformation`) now include new required fields `telephonePrefix` and `telephonePrefixCountryCode`
    - if you have custom forms that handle phone numbers, you need to add these fields and use the new `PhoneNumberInputControlled` component
- the `CreateOrderMutation` GraphQL variables `$telephone` and `$deliveryTelephone` changed from `String` to `PhoneDataInput` type (an object with `prefix`, `countryCode`, and `number` fields)
    - if you call the create order mutation directly or override `contactInformationUtils.ts` / `deliveryAddressUtils.ts`, update the telephone variables to use the new object format
- customer types (`DeliveryAddressType`, `CustomerUserType`, `CurrentCustomerType`) now include `telephonePrefix`, `telephonePrefixCountryCode`, and `telephoneNumber` fields
- `ContactInformation` store slice now includes `telephonePrefix`, `telephonePrefixCountryCode`, `deliveryTelephonePrefix`, and `deliveryTelephonePrefixCountryCode` — if you persist or restore contact information state, ensure these fields are included
- `PhoneNumberInputControlled` automatically detects international prefixes in pasted or autofilled phone numbers (e.g. `+421777123456`, `00420608913202`) and splits them into the prefix select and local number — this is handled by the new `parsePhoneWithPrefix` utility
- new components and utilities were added: `PhonePrefixSelect`, `PhoneNumberInputControlled`, `CountrySelectControlled`, `usePhonePrefixes` hook, `parsePhoneWithPrefix` utility, and `PhonePrefixesQuery` GraphQL query
- the minimum-length validation on the telephone number was removed — `validateTelephone` now only enforces digits-only and the existing 30-character maximum, so countries with shorter national numbers (e.g. Denmark, Iceland) are no longer rejected; the `telephoneMinLength` constant was dropped from `validationConstants.ts` together with its translation key
- the combobox input rendered by `Select` now opts out of password-manager autofill via `autoComplete="off"`, `data-lpignore`, `data-1p-ignore`, `data-bwignore`, and `data-form-type="other"` — this prevents LastPass / 1Password / Bitwarden / Dashlane from dropping stray characters into search/dropdown inputs (most visibly the phone-prefix selector)
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/40ded8e76d42bd51da3787ab2ba191ed3f41311b) to update your project

#### Public files cleanup ([#4539](https://github.com/shopsys/shopsys/pull/4539))

- removed unused static assets (images, icons, SVGs, DM Sans fonts)
- replaced placeholder images (404_m.png → 404.png, optimized-noimage.webp → noimage.webp)
- separated store logo from Shopsys brand logo (logo.svg vs shopsys-logo.svg), fixed missing slash in LogoMetadata
- replaced password eye SVG with EyeIcon/EyeCrossedIcon React components, wrapped in accessible <button> with aria-label
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/7c33f744f67e1caf96410dfb0fb0d0dbf2d1ab33) to update your project

#### Replace Eslint and Prettier with Biome ([#4543](https://github.com/shopsys/shopsys/pull/4543))

- ESLint, Prettier, and all their plugins have been removed from storefront dependencies
- Biome (`@biomejs/biome`) is now used for linting and formatting
- remove `eslint.config.mjs`, `prettier.config.js`, and `.prettierignore` from your storefront directory
- add `biome.json` and `biome/plugin-valid-interactive-content.grit` to your storefront directory (see #project-base-diff)
- update your `package.json` scripts:
    - `"format"` → `"biome format --write ."`
    - `"format-check"` → `"biome format ."`
    - `"lint"` → `"biome check ."`
    - `"lint--fix"` → `"biome check --write ."`
- remove ESLint/Prettier devDependencies: `@eslint/eslintrc`, `@eslint/js`, `eslint`, `eslint-config-prettier`, `eslint-plugin-jsx-a11y`, `eslint-plugin-no-relative-import-paths`, `eslint-plugin-react`, `eslint-plugin-react-hooks`, `eslint-plugin-unused-imports`, `globals`, `@typescript-eslint/eslint-plugin`, `@typescript-eslint/parser`, `prettier`, `prettier-plugin-tailwindcss`, `@trivago/prettier-plugin-sort-imports`
- add `@biomejs/biome` to devDependencies
- run `pnpm run lint--fix` to apply Biome auto-fixes and formatting to your codebase
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/0cf179ba246a3862dc814cf96ab5f8ee9b08b61c) to update your project

#### SSR and Error handling ([#4512](https://github.com/shopsys/shopsys/pull/4512))

- **`getIsRedirectedFromSsr()` was renamed to `isFullPageRequest()`**
    - before: `import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr'`
    - after: `import { isFullPageRequest } from 'utils/isFullPageRequest'`
- **`initServerSideProps` was split into `prefetchLayoutQueries` + `buildServerSideProps`**
    - `initServerSideProps` still exists as a convenience wrapper for simple pages (no page-specific queries)
    - friendly URL pages (categories, brands, flags, articles, blogArticles, blogCategories, products, stores) now use `prefetchLayoutQueries` + `buildServerSideProps` directly with `Promise.all` for parallel SSR data fetching
    - if your project has custom friendly URL pages that used `initServerSideProps` with page-specific queries passed via `prefetchedQueries`, update them to use the new parallel pattern:

    ```diff
    -import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
    +import { buildServerSideProps, prefetchLayoutQueries } from 'utils/serverSide/initServerSideProps';

    -const pageResponse = await client.query(PageQueryDocument, { urlSlug }).toPromise();
    -return initServerSideProps({ context, client, ssrExchange, domainConfig });
    +const [pageResponse, layoutResult] = await Promise.all([
    +    client.query(PageQueryDocument, { urlSlug }).toPromise(),
    +    prefetchLayoutQueries({ client, context, domainConfig }),
    +]);
    +return buildServerSideProps({ layoutResult, client, ssrExchange, context, domainConfig, pageQueryResults: [pageResponse] });
    ```

- **`initServerSideProps` has new optional `currentCustomerUserPrefetchMode` parameter**
    - defaults to `'auth'` (lightweight query fetching only `uuid`, `__typename`, and `roles`)
    - use `'full'` for pages that need full customer user data during SSR (e.g., customer section, order flow, contact form)
- **error handling on friendly URL pages now runs unconditionally**
    - the `if (getIsRedirectedFromSsr(context.req.headers))` guard was removed from all friendly URL pages
    - if your project has custom friendly URL pages with this guard, remove it so error handling works during both SSR and client-side navigation
- **graphcache now runs only on the client** — the `cache` exchange in `urql/exchanges.ts` is conditionally included only when `isClient` is true; if your project relies on graphcache normalization during SSR, update accordingly
- **`CookiesStoreProvider` prop `cookieStoreStateFromServer` is now optional** — falls back to `getDefaultCookiesStoreState()` when undefined
- **`getDefaultInitState` in `utils/cookies/cookiesStore.ts` was renamed to `getDefaultCookiesStoreState` and exported**
- **self-hosted DM Sans font files removed** (`public/fonts/dmSans*.woff2`) — fonts now use Google Fonts; font weight `500` removed from Inter and Raleway
- **`ToastContainer` is now lazy-loaded** — moved from direct import in `_app.tsx` to deferred dynamic component `DeferredToastContainer`; CSS imports (`react-toastify/dist/ReactToastify.css` and `nprogress/nprogress.css`) moved from `_app.tsx` to their respective usage sites
- **types `ServerSidePropsType`, `QueriesArray`, `InitServerSidePropsParameters` moved** from `utils/serverSide/initServerSideProps.ts` to `utils/serverSide/types.ts` — they are re-exported from `initServerSideProps.ts` for backward compatibility
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a59f122a050b5807443d915454916de56a9ddd3c) to update your project

#### Enforce unique parameter values at database level ([#4536](https://github.com/shopsys/shopsys/pull/4536))

- Frontend API `Parameter` type has a new non-nullable field `type: ParameterTypeEnum!` (values: `CHECKBOX`, `COLOR`, `SLIDER`)
    - add `type` to your `ParameterFragment` and regenerate GraphQL types
- in `ProductDetailParametersSection.tsx`, render `ColorPreview` only for parameters where `parameter.type === TypeParameterTypeEnum.Color` to avoid showing the color swatch for non-color parameters
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a3b8690130298a4a5831ab85469e3d61c5f2ed4a) to update your project

#### Last visited products now store more items than displayed ([#4549](https://github.com/shopsys/shopsys/pull/4549))

- the constant `LAST_VISITED_MAX_ITEMS` in `lastVisitedProductsUtils.ts` was removed and replaced with `LAST_VISITED_STORED_ITEMS` (15, private) and `LAST_VISITED_DISPLAYED_ITEMS` (10, exported)
    - if you relied on `LAST_VISITED_MAX_ITEMS`, use `LAST_VISITED_DISPLAYED_ITEMS` instead
- the last visited products cookie now stores up to 15 items, but only 10 are displayed, so hidden or unavailable products no longer reduce the visible count
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b533659af44e746d9bc3897adf3c22fd4346f4d6) to update your project

#### Uploaded files can be viewed directly in browser ([#3589](https://github.com/shopsys/shopsys/pull/3589))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/56862ba98ec0a23ea6e29c8c948402ec9efb420f) to update your project

#### useErrorHandler now applies customMessage only for application toasts ([#4557](https://github.com/shopsys/shopsys/pull/4557))

- `customMessage` is now applied only to application errors (`application` toast)
- validation errors keep their specific validation messages and are not overridden by a generic message
- we added toast origin distinction (`validation` / `application` / `network`) and tests for the mixed-error scenario
- when a response contained both validation and application errors, the validation message was overridden by `customMessage`, which was confusing for users
- the goal is to keep precise form validation feedback while still allowing a user-friendly generic message for application errors
- see #project-base-diff

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/b4071cbf46ab3e6540e054c1a099c9893c8d4785) to update your project

#### SEO category filters now keep latest state on rapid toggles ([#4559](https://github.com/shopsys/shopsys/pull/4559))

- Fixed SEO category filter query merging so rapid repeated toggles keep the latest state.
- Prevented stale query params from re-introducing removed SEO-sensitive values (flags, brands, and checkbox parameters).
- Added regression tests for rapid toggling scenarios.
- see #project-base-diff

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f0c0fe8a494999abeae0e1656955abce741bca8a) to update your project

#### token auth errors now preserve expired-token across backend and storefront ([#4560](https://github.com/shopsys/shopsys/pull/4560))

- if you override `project-base/storefront/urql/authExchange.ts`, `project-base/storefront/urql/errorExchange.ts`, or `project-base/storefront/utils/errors/handleServerSideErrorResponseForFriendlyUrls.ts`, update your auth handling to treat GraphQL error `extensions.userCode` values `expired-token` and `invalid-token` as authentication failures even when the response status is `200`
- if you override `project-base/storefront/utils/errors/applicationErrors.ts`, add `expired-token` to the no-log application error codes
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d5f3893e7407463eacec14ea734aa1325c0f1b2f) to update your project

#### Optimize GitLab Cypress failure artifacts ([#4566](https://github.com/shopsys/shopsys/pull/4566))

Previously, `test:storefront-acceptance` failed with `413 Request Entity Too Large` when uploading its failure artifacts — the combined size of videos, screenshots, and snapshotDiffs for the full Cypress suite exceeded the runner's upload limit, leaving developers with no artifacts to diagnose the failure.

The fix uses an `after:spec` hook in `cypress.config.ts` to delete the video file of any spec where all tests passed. Failure artifacts are then naturally scoped to the spec files that actually failed:

- `videos/` — only kept for spec files with at least one failing test (or a test that needed retries)
- `screenshots/` — Cypress already captures these only on failure (no change needed)
- `snapshotDiffs/` — already only generated on visual regression failure (no change needed)

`test:storefront-acceptance` now uploads full failure artifacts (`videos`, `screenshots`, `snapshotDiffs`, and service logs `php-fpm`/`webserver`/`storefront`) on failure. Because videos are filtered to only failing specs, total artifact size stays well under the upload limit in normal failure scenarios.

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/2a1406e274fcbe1c3d65acf6d3759b8ea4edaee8) to update your project

#### AddToCartPopup: redesigned layout for better cart button visibility ([#4575](https://github.com/shopsys/shopsys/pull/4575))

- if you use `<ProductGift>` in custom code, update its props — the prop `gift: TypeProductGiftFragment` (with optional `variant`) was replaced by `gifts: TypeProductGiftFragment[]` and the component now iterates internally:
    - before: `<ProductGift gift={gift} variant="default" />`
    - after: `<ProductGift gifts={product.gifts} />`
- if you use `<DeferredRecommendedProducts>` in custom pages, wrap it in `<Webline>` inside the `render` callback — the component no longer emits its own `<Webline>` wrapper (see `HomePageContent.tsx`, `CartContent.tsx`, `ProductDetailContent.tsx` for reference)
- if your custom translations reference the removed keys `Gifts` (common) and `Recommended products` (accessibility), remove them
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/34622f3230451079f75e42d2738dd963e972fcb9) to update your project

#### Fix cypress gitlab flow ([#4580](https://github.com/shopsys/shopsys/pull/4580))

- Cypress spec folders were renamed so complaints sort alphabetically before order-creating B2B specs (required for serial CI with a shared DB):
    - `project-base/storefront/cypress/e2e/b2b/` → `project-base/storefront/cypress/e2e/b2bUser/`
    - `project-base/storefront/cypress/e2e/complaints/` → `project-base/storefront/cypress/e2e/b2bComplaints/`
- If you have custom Cypress specs importing from the old paths (e.g. `e2e/b2b/b2bSupport`, `e2e/complaints/complaintsSupport`), update those imports to the new paths.
- If you have custom entries in `cypress.config.ts` `patternsMap` referencing `e2e/b2b/*.cy.ts` or `e2e/complaints/*.cy.ts`, update them to `e2e/b2bUser/*.cy.ts` and `e2e/b2bComplaints/*.cy.ts`.
- Existing snapshot folders under `cypress/snapshots/e2e/b2b/` and `cypress/snapshots/e2e/complaints/` were moved to match the new spec paths.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/5bbcb4b284cfc7a34ecb6c58a0043f3eea10ba52) to update your project

#### UserConsentForm: GTM consent update event now uses current form values ([#4585](https://github.com/shopsys/shopsys/pull/4585))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/f61c60ed72633aeb74ee484f91921ace3af108d1) to update your project

#### added GTM noscript iframe fallback ([#4586](https://github.com/shopsys/shopsys/pull/4586))

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/627ec66dbebcca8153effe6a21b9f4c66a6fda74) to update your project

#### storefront GTM page events now use updated metadata ([#4591](https://github.com/shopsys/shopsys/pull/4591))

- GTM page view event was renamed from `page_view` to `page_ready`
    - if your project uses custom GTM triggers, update them to listen for `page_ready`
    - if your project references `GtmEventType.page_view`, replace it with `GtmEventType.page_ready`
- storefront GTM page-ready helpers and types were renamed from `PageView` to `PageReady`
    - replace `GtmPageViewEventType` with `GtmPageReadyEventType`
    - replace `getGtmPageViewEvent`, `useGtmPageViewEvent`, `useGtmStaticPageViewEvent`, and `useGtmFriendlyPageViewEvent` with their `PageReady` variants
    - update imports from `gtm/utils/pageViewEvents/...` to `gtm/utils/pageReadyEvents/...`
- GTM event enum members `GtmEventType.payment_and_transport_page_view` and `GtmEventType.contact_information_page_view` were renamed to `GtmEventType.payment_and_transport_view` and `GtmEventType.contact_information_view`
    - the emitted event values remain `ec.payment_and_transport_view` and `ec.contact_information_view`
- storefront GTM payment/transport and contact-information helpers and types were renamed from `PageViewEvent` to `ViewEvent`
- `page.articleId` in GTM page metadata now uses UUID string values for article detail and blog article detail pages
    - update custom storefront GTM typings or consumers that expected numeric blog article IDs
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/d8ee8dd6b554a2f5152f16c90c6652f260ad7dac) to update your project

#### Autocomplete GTM reports found result counts ([#4593](https://github.com/shopsys/shopsys/pull/4593))

- `ec.autocomplete_results_view` GTM event now reports found result counts in `autocompleteResults.results` and `autocompleteResults.sections.product/category` when the search provider provides total counts
- if your GTM setup expects displayed autocomplete item counts, update it to use the new found-result semantics
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/253333f3cc09555a7076609844ed9b34e8618589) to update your project

#### Add wishlist and comparison GTM events ([#4594](https://github.com/shopsys/shopsys/pull/4594))

- `toggleProductInWishlist()` returned by `useWishlist()` now requires product data and GTM list context instead of only product UUID
    - before: `toggleProductInWishlist(productUuid)`
    - after: `toggleProductInWishlist(product, gtmProductListName, listIndex?)`
- `toggleProductInComparison()` returned by `useComparison()` now requires product data and GTM list context instead of only product UUID
    - before: `toggleProductInComparison(productUuid)`
    - after: `toggleProductInComparison(product, gtmProductListName, listIndex?)`
- wishlist and comparison product list GTM events now share the same product-list GTM helper and push `ec.add_to_wishlist`, `ec.remove_from_wishlist`, `ec.add_to_comparsion`, and `ec.remove_from_comparsion`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/9a26b144ddcc07c36488153376689a33fe18087d) to update your project

#### Add company identifiers to user entry GTM events ([#4595](https://github.com/shopsys/shopsys/pull/4595))

- `ec.login` and `ec.registration` GTM events now include `user.companyNumber` and `user.companyVatNumber` when company customer data is available
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/dacdd3ac7bb8c6f05698924270480b268dcf93fc) to update your project

#### Add variant parameters to GTM cart products ([#4597](https://github.com/shopsys/shopsys/pull/4597))

- GTM cart product items now include variant parameter values in the `variant` field for variant products
- parameter values with units are formatted together, for example `width: 10 cm`, and multiple values are separated by commas
- see #project-base-diff

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e86f540c558f3cd1e8b7d953442e2bf5e3fc19aa) to update your project

#### Add product ecommerce data to watchdog GTM event ([#4598](https://github.com/shopsys/shopsys/pull/4598))

See #project-base-diff to update your project.

- see [project-base diff](https://www.github.com/shopsys/project-base/commit/e4dc657c1a42666b9f0b123b93d6ecedada3e9c1) to update your project

#### Complete GTM user data from prioritized sources ([#4599](https://github.com/shopsys/shopsys/pull/4599))

- GTM `page_ready` and `ec.create_order` events now include additional `user.*` fields and resolve user address data from event-specific prioritized sources
- `ec.create_order` resolves address data from pickup place, order delivery address, and order billing address
- `page_ready` resolves address data only from account billing address and account default delivery address, so it no longer keeps delivery or billing data from the current or previous order
- `user.street` contains the street name without house number, while the separated house number is sent in `user.streetNumber`
- `user.telephone` and order confirmation phone numbers are normalized without whitespace
- the user IP field is named `ipAddress` and is populated only from a validated forwarded or remote IP address
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/33eea6bc47859556efa0e6b715216b5a49806494) to update your project

#### create order GTM now reports submitted order data ([#4602](https://github.com/shopsys/shopsys/pull/4602))

- `ec.create_order` GTM event now includes transport price without VAT, transport price with VAT, transport type, discount amount, and promo codes
- `reviewConsents` in the `ec.create_order` GTM event are now derived from the satisfaction questionnaire checkbox in the checkout contact information step
- `newsletterSubscription` in the `ec.create_order` GTM event now reflects the value submitted in the checkout form instead of the stored customer profile value
- the satisfaction questionnaire checkbox text was updated to mention Heureka, Zboží, and Google while keeping the existing Heureka agreement behavior
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/8719d12de824bbf5bed03f95adb7eeaa8d1f7799) to update your project

#### Add order withdrawal GTM event ([#4603](https://github.com/shopsys/shopsys/pull/4603))

- Storefront now pushes the GTM `ec.withdrawal` event after an order withdrawal request is submitted successfully
- the event contains the order number in `ecommerce.id`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/bf45f25acfb11fd8630e2d04dd1bd3e8a4cf1235) to update your project

#### Catalog page now shows categories independently of navigation ([#4606](https://github.com/shopsys/shopsys/pull/4606))

- the catalog page now uses the existing categories query instead of the navigation menu query
- the catalog page remains available with visible top-level categories even when the `/catalog` URL is not configured in the navigation menu
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/70d90b869a863b8eca69f62492893327e7a104bf) to update your project

#### Cache storefront translations across route changes ([#4610](https://github.com/shopsys/shopsys/pull/4610))

- Storefront now keeps translations cached on the client and no longer sends full translation namespaces in every Next route-data response
- custom usages of `buildServerSideProps()` and `initServerSideProps()` need to pass the shared server-side Redis client so the translation version can be included in route-data responses
- if your project customizes `_app.tsx`, wrap the application with `CachedI18nProvider` so client-side route changes can reuse and refresh cached translations
- if your project customizes the nginx configuration for `/content/locales/`, return an empty JSON object for missing user translation override files instead of rendering a storefront error page
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/a6c885ac1ef239d17f3bf0abad19401edda91718) to update your project

#### Keep delivery addresses synced in checkout ([#4612](https://github.com/shopsys/shopsys/pull/4612))

- newly created delivery addresses are now added to the customer user cache immediately after the create mutation
- deleted delivery addresses are now removed from the customer user cache immediately after the delete mutation
- checkout now persists the automatically selected delivery address so the new address can be used for the order without reloading the page
- checkout now validates that logged-in customers add or select a delivery address before submitting the order when sending it to another delivery address
- phone prefix and phone data results now have explicit graphcache key configuration
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/1a125a66ca5d5aae9c7762f35dcc19d0984288ee) to update your project

#### Allow numbered street names in address forms ([#4615](https://github.com/shopsys/shopsys/pull/4615))

- street validation now accepts valid street names that start with ordinal numbers, such as `17. listopadu 2/2`
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/125327d583d916a738e54678e232d936ee9a4bd7) to update your project

#### Fix skeletons during browser history navigation ([#4616](https://github.com/shopsys/shopsys/pull/4616))

- The storefront page loading state now keeps the intended skeleton type in browser history entries and restores it when users navigate with browser back and forward buttons.
- This prevents showing a skeleton for the previously visited page, for example a product detail skeleton while returning to a category or homepage.
- The change also avoids showing a full-page skeleton for shallow same-route history changes, such as category filtering, sorting, or pagination.
- If your project customizes storefront navigation, page loading, or skeleton handling, review #project-base-diff and port the changes from `usePageLoader()` and the related skeleton route state helpers.
- see [project-base diff](https://www.github.com/shopsys/project-base/commit/4c1a44d5f956cf9879d5bd2a1001f550dd4ef8ac) to update your project

<!-- storefrontNotes -->
