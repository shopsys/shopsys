# Adding New Attribute to an Entity

In the following example, we will add the `extId` (alias "external ID") field to the `Product` entity.
It is a common modification when you need your e-commerce application and ERP system to co-work smoothly.

## Extend framework `Product` entity

!!! tip

    "How does the entity extension work?"<br>
    Find it out in the [separate article](../extensibility/entity-extension.md).<br>
    Most common entities (including `Product`) are already extended in `project-base` to ease your development.<br>
    However, when extending any other entity, there are [few more steps](../extensibility/entity-extension.md#how-can-i-extend-an-entity) that need to be done.

Add new `extId` field with Doctrine ORM attributes and a getter for the field into `App\Model\Product\Product` class.

Overwrite `setData` method for setting entity data from a data object.

```php
namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

#[ORM\Table(name: 'products')]
#[ORM\Entity]
class Product extends BaseProduct
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $extId;

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function setData(BaseProductData $productData): void
    {
        parent::setData($productData);

        $this->extId = $productData->extId ?? 0;

    }

    /**
     * @return int
     */
    public function getExtId(): int
    {
        return $this->extId;
    }
}
```

_Notice that type hints and docblock annotations of the methods do not match.
This is on purpose - extended class must respect the interface of its parent while docblock annotation ensures proper IDE autocomplete._

### Database migrations

Generate a [database migration](../introduction/database-migrations.md) creating a new column for the field by running:

```sh
php phing db-migrations-generate
```

The command prints a file name the migration was generated into:

```text
Checking database schema...
Database schema is not satisfying ORM, a new migration was generated!
Migration file ".../src/Migrations/Version20180503133713.php" was saved (525 B).
```

As you are adding a not nullable field, you need to modify the generated migration manually
and add a default value for already existing entries:

```php
$this->sql('ALTER TABLE products ADD ext_id INT NOT NULL DEFAULT 0');
$this->sql('ALTER TABLE products ALTER ext_id DROP DEFAULT');
```

!!! hint

    In this step, you were using Phing target `db-migrations-generate`.<br>
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md).

Run the migration to create the column in your database:

```
php phing db-migrations
```

### ProductData class

Add public `extId` field into `App\Model\Product\ProductData` class.

```php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

class ProductData extends BaseProductData
{
    /**
     * @var int
     */
    public $extId;
}
```

### ProductDataFactory class

In the following steps, we will overwrite all services that are responsible
for `Product` and `ProductData` instantiation to make them take our new attribute into account.

Edit `App\Model\Product\ProductDataFactory` - overwrite `create()` and `createFromProduct()` methods.

```php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;

class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @return \App\Model\Product\ProductData
     */
    protected function createInstance(): BaseProductData
    {
        return new ProductData();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductData
     */
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        $productData = $this->createInstance();
        $this->fillFromProduct($productData, $product);
        $productData->extId = $product->getExtId() ?? 0;

        return $productData;
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    public function create(): BaseProductData
    {
        $productData = $this->createInstance();
        $this->fillNew($productData);
        $productData->extId = 0;

        return $productData;
    }
}
```

Your `ProductDataFactory` is already registered in [`services.yaml`]({{github.link}}/project-base/app/config/services.yaml)
as an alias for the original class.

```yaml
Shopsys\FrameworkBundle\Model\Product\ProductDataFactory:
    alias: App\Model\Product\ProductDataFactory
```

## Enable an administrator to edit the `extId` field

Add your `extId` field into the form by editing `ProductFormTypeExtension` in `App\Form\Admin` namespace.
The original `ProductFormType` is set as the extended type by the implementation of `getExtendedType()` method.

```php
namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

final class ProductFormTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // "basicInformationGroup" is defined in ProductFormType
        $basicInformationGroup = $builder->get('basicInformationGroup');
        $basicInformationGroup->add('extId', IntegerType::class, [
            'required' => true,
            'constraints' => [
                new Constraints\NotBlank(message: 'Please enter external ID'),
            ],
            'label' => 'External ID',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductFormType::class;
    }
}
```

!!! tip

    If you want to change the order for your newly created field, please look at the section [Changing order of groups and fields](../extensibility/form-extension.md#changing-order-of-groups-and-fields)

Overwrite the `setData()` method in your `Product` class.

```php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

// ...

/**
 * @param \App\Model\Product\ProductData $productData
 */
public function setData(BaseProductData $productData) {
    parent::setData($productData);

    $this->extId = $productData->extId;
}
```

In your `ProductDataFactory` class, update the `createFromProduct()` method so it sets your new `extId` field.

```php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

// ...

class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductData
     */
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        $productData = $this->createInstance();
        $this->fillFromProduct($productData, $product);
        $productData->extId = $product->getExtId();

        return $productData;
    }

    // ...
}
```

## GraphQL Frontend API

To add your new attribute into the frontend API, so it can be displayed on the storefront, you need to modify the API definition, which is
already a part of your open-box project base: [`Product.types.yaml`]({{github.link}}/project-base/app/config/graphql/types/ModelType/Product/Product.types.yaml).

```yaml
Product:
    # ...
    config:
        fields:
            # ...
            extId:
                type: 'String'
                description: 'External ID of the product'
```

The corresponding product data are then fetched automatically from the `Product` entity or from the elasticsearch index.
For more information about the API, refer to [the frontend-api docs](../frontend-api/introduction-to-frontend-api.md).

## Export data to elasticsearch index

To export your new `extId` field to the elasticsearch index, add it to a product export data provider implementing [`ProductExportDataProviderInterface`]({{github.link}}/packages/framework/src/Model/Product/Elasticsearch/ProductExportDataProviderInterface.php).
In most projects, this means updating the existing [`ProductExportDataProvider`]({{github.link}}/project-base/app/src/Model/Product/Elasticsearch/ProductExportDataProvider.php).
Create a separate provider only when the field belongs to a separate package/integration or needs its own dependencies and batch loading logic.

```diff
final class ProductExportDataProvider implements ProductExportDataProviderInterface
{
+    public const string EXT_ID = 'extId';
+    public const string EXT_ID_SCOPE = 'product_ext_id_scope';

     /**
      * {@inheritdoc}
      */
     #[Override]
     public function getExportFields(): array
     {
         return [
             // ...
+            self::EXT_ID,
         ];
     }

     /**
      * {@inheritdoc}
      */
     #[Override]
     public function getExportScopeRules(): array
     {
         return [
             // ...
+            self::EXT_ID_SCOPE => new ProductExportScopeRule([self::EXT_ID]),
         ];
     }

     /**
      * {@inheritdoc}
      */
     #[Override]
     public function getExportedFieldValue(BaseProduct $product, int $domainId, string $locale, string $field): mixed
     {
         if (!$product instanceof Product) {
             throw new InvalidArgumentException(sprintf('Product must be instance of "%s"', Product::class));
         }

         return match ($field) {
             // ...
+            self::EXT_ID => $product->getExtId(),
             default => throw new InvalidArgumentException(sprintf('There is no definition for exporting "%s" field to Elasticsearch', $field)),
         };
     }

     #[Override]
     public function getDefaultValue(string $field): mixed
     {
         return match ($field) {
             // ...
+            self::EXT_ID => null,
             default => throw new InvalidArgumentException(sprintf('There is no default value for "%s" Elasticsearch field', $field)),
         };
     }
}
```

If the new field is needed only during full exports and full recalculations, it's not necessary to mention it in `getExportScopeRules()`.
If it belongs to an existing scoped recalculation, use the existing scope name in `getExportScopeRules()`.
If it needs to be refreshed independently, create a new scope as shown above.
When you create a new provider class, the service is autoconfigured automatically when it implements `ProductExportDataProviderInterface`.

For more information about the elasticsearch usage in Shopsys Platform, check the [Elasticsearch documentation section](../model/elasticsearch.md).

## Storefront

Now, we will display the `extId` in the storefront on the product detail page. First, we need to extend the GraphQL query to include the `extId` field.
Edit [`ProductDetailInterfaceFragment`]({{github.link}}/project-base/storefront/graphql/requests/products/fragments/ProductDetailInterfaceFragment.graphql) file in your project:

```diff
fragment ProductDetailInterfaceFragment on Product {
    // ...
+   extId
}
```

Finally, you can use the `extId` within a product detail component:

```tsx
{
    product.extId && (
        <div>
            {t('External ID')}: {product.extId}
        </div>
    );
}
```

## Data fixtures

You can modify data fixtures in `src/DataFixtures/` of your project.

### Random `extId`

If you want to add a unique random `extId` for products from data fixtures, you can add it in the `createProduct` method of [`ProductDataFixture.php`]({{github.link}}/project-base/app/src/DataFixtures/Demo/ProductDataFixture.php).
You can use [`Faker`](https://github.com/FakerPHP/Faker/) to generate random numbers like this:

```diff
+   use Faker\Generator as Faker;

    //...

+   /**
+    * @var \Faker\Generator
+    */
+   protected $faker;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVariantFacade $productVariantFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \App\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceConverter $priceConverter
+    * @param \Faker\Generator $faker
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductVariantFacade $productVariantFacade,
        Domain $domain,
        PricingGroupFacade $pricingGroupFacade,
        ProductDataFactory $productDataFactory,
        ProductParameterValueDataFactory $productParameterValueDataFactory,
        ParameterValueDataFactory $parameterValueDataFactory,
        ParameterFacade $parameterFacade,
        ParameterDataFactory $parameterDataFactory,
-       PriceConverter $priceConverter
+       PriceConverter $priceConverter,
+       Faker $faker
    ) {
        $this->productFacade = $productFacade;
        $this->productVariantFacade = $productVariantFacade;
        $this->domain = $domain;
        $this->pricingGroupFacade = $pricingGroupFacade;
        $this->productDataFactory = $productDataFactory;
        $this->productParameterValueDataFactory = $productParameterValueDataFactory;
        $this->parameterValueDataFactory = $parameterValueDataFactory;
        $this->parameterFacade = $parameterFacade;
        $this->parameterDataFactory = $parameterDataFactory;
        $this->priceConverter = $priceConverter;
+       $this->faker = $faker;
    }

    //...

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \App\Model\Product\Product
     */
    protected function createProduct(ProductData $productData): Product
    {
+       $productData->extId = $this->faker->unique()->numberBetween(1, 10000);
        /** @var \App\Model\Product\Product $product */
        $product = $this->productFacade->create($productData);

        $this->addProductReference($product);

        return $product;
    }
```

### Specific `extId`

If you need to add specific `extId` to products in the data fixture, you will have to update the creation of products in [`ProductDataFixture::load`]({{github.link}}/project-base/app/src/DataFixtures/Demo/ProductDataFixture.php).

```diff

    //...

    $productData = $this->productDataFactory->create();

    $productData->catnum = '9184440';
    $productData->partno = '8328B006';
    $productData->ean = '8845781245936';
+   $productData->extId = 1;

    //...
```
