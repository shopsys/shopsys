# Adding New Attribute to an Entity
In the following example, we will add `extId` (alias "external ID") field to the `Product` entity.
It is a common modification when you need your ecommerce application and ERP system to co-work smoothly.

!!! note
    If you want to display your new attribute on the frontend product list, you need to extend the [read model layer](../model/introduction-to-read-model.md) as well.
    You can find instructions in [Extending Product List](./extending-product-list.md).

## Extend framework `Product` entity

!!! tip "How does the entity extension work?"
    Find it out in the [separate article](../extensibility/entity-extension.md).
    Most common entities (including `Product`) are already extended in `project-base` to ease your development.
    However, when extending any other entity, there are [few more steps](../extensibility/entity-extension.md#how-can-i-extend-an-entity) that need to be done.

Add new `extId` field with Doctrine ORM annotations and a getter for the field into `App\Model\Product\Product` class.

Overwrite constructor for creating `Product` instances.

```php
namespace App\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity
 */
class Product extends BaseProduct
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $extId;

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product[]|null $variants
     */
    protected function __construct(BaseProductData $productData, array $variants = null)
    {
        parent::__construct($productData, $variants);

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

_Notice that type hints and annotations of the methods do not match.
This is on purpose - extended class must respect interface of its parent while annotation ensures proper IDE autocomplete._

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

As you are adding not nullable field, you need to manually modify the generated migration
and add a default value for already existing entries:

```php
$this->sql('ALTER TABLE products ADD ext_id INT NOT NULL DEFAULT 0');
$this->sql('ALTER TABLE products ALTER ext_id DROP DEFAULT');
```

!!! hint
    In this step you were using Phing target `db-migrations-generate`.  
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)_

Run the migration to actually create the column in your database:

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

*Alternatively you can create an independent class by implementing
[`Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface`](https://github.com/shopsys/shopsys/blob/9.0/packages/framework/src/Model/Product/ProductDataFactoryInterface.php).*

```php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;

class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductData
     */
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        $productData = new ProductData();
        $this->fillFromProduct($productData, $product);
        $productData->extId = $product->getExtId() ?? 0;

        return $productData;
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    public function create(): BaseProductData
    {
        $productData = new ProductData();
        $this->fillNew($productData);
        $productData->extId = 0;

        return $productData;
    }
}
```

Your `ProductDataFactory` is already registered in [`services.yaml`](https://github.com/shopsys/shopsys/blob/9.0/project-base/config/services.yaml)
as an alias for the original interface.

```yaml
Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface: '@App\Model\Product\ProductDataFactory'
```

## Enable administrator to edit the `extId` field

Add your `extId` field into the form by editing `ProductFormTypeExtension` in `App\Form\Admin` namespace.
The original `ProductFormType` is set as the extended type by implementation of `getExtendedType()` method.

```php
namespace App\Form\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Product\ProductFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

class ProductFormTypeExtension extends AbstractTypeExtension
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
                new Constraints\NotBlank(['message' => 'Please enter external ID']),
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
    If you want to change order for your newly created field, please look at section [Changing order of groups and fields](../extensibility/form-extension.md#changing-order-of-groups-and-fields)

In your `Product` class, overwrite the `edit()` method.

```php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

// ...

/**
 * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
 * @param \App\Model\Product\ProductData $productData
 */
public function edit(
    array $productCategoryDomains  
    BaseProductData $productData,
) {
    parent::edit($productCategoryDomains, $productData);

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
        $productData = new ProductData();
        $this->fillFromProduct($productData, $product);
        $productData->extId = $product->getExtId();

        return $productData;
    }

    // ...
}
```

## Front-end
In order to display your new attribute on a front-end page, you can modify the corresponding template directly
as it is a part of your open-box, eg. [`detail.html.twig`](https://github.com/shopsys/shopsys/blob/9.0/project-base/templates/Front/Content/Product/detail.html.twig).

```twig
{{ product.extId }}
```

## Data fixtures

You can modify data fixtures in `src/DataFixtures/` of your project

### Random `extId`

If you want to add unique random `extId` for products from data fixtures you can add it in `createProduct` method of [`ProductDataFixture.php`](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/DataFixtures/Demo/ProductDataFixture.php).
You can use [`Faker`](https://github.com/fzaninotto/Faker) to generate random numbers like this:

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
        ProductDataFactoryInterface $productDataFactory,
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

If you need to add specific `extId` to products in data fixture you will have to update creation of products in [`ProductDataFixture::load`](https://github.com/shopsys/shopsys/blob/9.0/project-base/src/DataFixtures/Demo/ProductDataFixture.php).

```diff

    //...

    $productData = $this->productDataFactory->create();

    $productData->catnum = '9184440';
    $productData->partno = '8328B006';
    $productData->ean = '8845781245936';
+   $productData->extId = 1;

    //...
```
