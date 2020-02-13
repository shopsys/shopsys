# Basic Data Import

This cookbook will guide you through the process of importing data into your e-shop from an external source,
e.g. from an information system.

After completing this cookbook you should know:

- how to implement basic data import
- what are the best practices for importing data
- what are the pitfalls of importing data and how can you deal with them

## Example: Products import step by step

Let us create simple products import from an external source.
The source is mocked with [apiari.io service](http://docs.ssfwbasicdataimportdemo.apiary.io/) which returns example products data in JSON format:

```json
[
    {
        "id": 1001,
        "name": "Emos LED light",
        "price_without_vat": "354",
        "vat_percent": "21",
        "ean": "12345678901",
        "brand_id": 101,
        "description": "light source: 1 x 3W CREE LED + 12 LED 5 mm ...",
        "stock_quantity": 25
    },
    {
        "id": 1002,
        "name": "Digital camera EasyPix GoXtreme Impact, Full HD Action 1080p, white",
        "price_without_vat": "2050",
        "vat_percent": "21",
        "ean": "12345678902",
        "brand_id": 102,
        "description": "EasyPix GoXtreme - outdoor waterproof camera.",
        "stock_quantity": 18
    },
    {
        "id": 1004,
        "name": "Power gril PARTY TIME Tescoma",
        "price_without_vat": "2478.51",
        "vat_percent": "21",
        "ean": "12345678904",
        "brand_id": 103,
        "description": "Highly powerful mobile grill with turbo vent.",
        "stock_quantity": 3
    }
]
```

### Step 1 - Add `$extId` to [Product](https://github.com/shopsys/shopsys/blob/master/project-base/src/Model/Product/Product.php) [entity](../model/introduction-to-model-architecture.md#entity)

We need to store the relation between your application database and the external source of data because later, in data transfer processing,
we will be deciding whether to create a new product or update existing one, based on the `$extId` attribute.
If you do not know how to add an attribute to an entity, take a look at [the cookbook](adding-new-attribute-to-an-entity.md).

### Step 2 - Create new cron module

Cron modules are the best way to handle data downloaded from external sources
because they can be scheduled, run on background and even iterated when necessary.

#### 2.1 - Add new `ImportProductsCronModule` class that implements [`SimpleCronModuleInterface`](https://github.com/shopsys/shopsys/blob/master/packages/plugin-interface/src/Cron/SimpleCronModuleInterface.php)

```php
// src/Model/Product/ImportProductsCronModule.php

namespace App\Model\Product;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImportProductsCronModule implements SimpleCronModuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
    }
}
```

!!! warning
    Cron modules are not suitable for data transfers initialized by an external source, you should implement Web Services for that purpose.

#### 2.2 - Add cron configuration to [`cron.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services/cron.yml)
```diff
# config/services/cron.yml

services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

+   App\Model\Product\ImportProductsCronModule:
+       tags:
+           - { name: shopsys.cron, hours: '*', minutes: '*' }
```

*Note: You can schedule cron modules to run at any time you want using [cron expression](https://en.wikipedia.org/wiki/Cron#CRON_expression). This example module will be run every 3 hours.*

### Step 3 - Process incoming data in our new `ImportProductsCronModule`
In this step, we will download data from the external source
and then decide whether to create or update existing product based on `$extId`.

#### 3.2 - Download external data and create or update product based on `$extId`
If a product exists with given `$extId` we modify it, otherwise, we need to create a new one.
```php
// src/Model/Product/ImportProductsCronModule.php

// ...

use App\Model\Product\ProductFacade;

// ...

const PRODUCT_DATA_URL = 'https://private-anon-38d0154157-ssfwbasicdataimportdemo.apiary-mock.com/products';

// ...

/**
 * @var \App\Model\Product\ProductFacade
 */
private $productFacade;

// ...

/**
 * @param \App\Model\Product\ProductFacade $productFacade
 */
public function __construct(
    ProductFacade $productFacade
) {
    $this->productFacade = $productFacade;
}

public function run()
{
    $externalProductsJsonData = file_get_contents(self::PRODUCT_DATA_URL);
    $externalProductsData = json_decode($externalProductsJsonData, true);
    $this->importExternalProductsData($externalProductsData);
}

/**
 * @param array $externalProductsData
 */
private function importExternalProductsData(array $externalProductsData)
{
    foreach ($externalProductsData as $externalProductData) {
        $extId = $externalProductData['id'];

        $product = $this->productFacade->findByExternalId($extId); // will be implemented in next steps
        if ($product === null) {
           $this->createProduct($externalProductData); // will be implemented in next steps
        } else {
           $this->editProduct($product, $externalProductData); // will be implemented in next steps
        }
    }
}
```

!!! note
    We need to know whether the product with given `$extId` exists.  
    For that purpose, we will use a descendant of [`ProductFacade`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductFacade.php) ([more about facades](../model/introduction-to-model-architecture.md#facade))
    which will use a descendant of [`ProductRepository`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductRepository.php) ([more about repositories](../model/introduction-to-model-architecture.md#repository))
    that can talk to the persistence layer.
    We will extend the framework classes and implement new methods in the next two steps.

#### 3.2 - Extend [`ProductRepository`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductRepository.php) and implement method `findByExternalId()` in order to be able find a [`Product`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/Product.php) by an external ID
Create new class `App/Model/Product/ProductRepository` that extends `ProductRepository` from the framework.
```php
// src/Model/Product/ProductRepository.php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductRepository as BaseProductRepository;

class ProductRepository extends BaseProductRepository
{
    /**
     * @param int $extId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function findByExternalId($extId)
    {
        return $this->getProductRepository()->findOneBy(['extId' => $extId]);
    }
}
```

Add information about the class extension into the container configuration in [`services.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services.yaml).
To [make the service public in TEST environment](../introduction/faq-and-common-issues.md#what-is-the-configuration-file-services_testyml-good-for), you need to add the same line into [`services_test.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services_test.yml) as well.

```yaml
Shopsys\FrameworkBundle\Model\Product\ProductRepository: '@App\Model\Product\ProductRepository'
```

#### 3.3 - Extend [`ProductFacade`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductFacade.php) and implement method `findByExternalId()` in order to get [`Product`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/Product.php) from repository

Create new class `App/Model/Product/ProductFacade` that extends `ProductFacade` from the framework.

```php
// src/Model/Product/ProductFacade.php
namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

class ProductFacade extends BaseProductFacade
{
    /**
     * @var \App\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @param int $extId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public function findByExternalId($extId)
    {
        return $this->productRepository->findByExternalId($extId);
    }
}
```

!!! tip
    You should overwrite `protected $productRepository` annotation so IDE knows that you are using the extended `ProductRepository`

Add information about the class extension into the container configuration in [`services.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services.yaml).
To [make the service public in TEST environment](../introduction/faq-and-common-issues.md#what-is-the-configuration-file-services_testyml-good-for),
you need to add the same line into [`services_test.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services_test.yml) as well.

```yaml
Shopsys\FrameworkBundle\Model\Product\ProductFacade: '@App\Model\Product\ProductFacade'
```

#### 3.4 - Implement `ImportProductsCronModule::createProduct()` and `ImportProductsCronModule::updateProduct()`

As an entry-point for data processing in Shopsys Framework, we use facades.
In this case, our new `ProductFacade`
and its inherited methods `create()` and `edit()`.
Those methods expect [`ProductData`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductData.php)
class as a parameter, you can use [`ProductDataFactory`](https://github.com/shopsys/shopsys/blob/master/project-base/src/Model/Product/ProductDataFactory.php) to create it.

```php
// src/Model/Product/ImportProductsCronModule.php

// ...

use App\Model\Product\ProductDataFactory;

// ...

/**
 * @var \App\Model\Product\ProductDataFactory
 */
private $productDataFactory;

// ...

/**
 * @param \App\Model\Product\ProductFacade $productFacade
 * @param \App\Model\Product\ProductDataFactory $productDataFactory
 */
public function __construct(
    ProductFacade $productFacade,
    ProductDataFactory $productDataFactory
)
{
    $this->productFacade = $productFacade;
    $this->productDataFactory = $productDataFactory;
}

// ...

/**
 * @param array $externalProductData
 */
private function createProduct(array $externalProductData) {
    $productData = $this->productDataFactory->create();
    $this->fillProductData($productData, $externalProductData); // will be implemented in next step

    $this->productFacade->create($productData);
}

/**
 * @param \App\Model\Product\Product $product
 * @param array $externalProductData
 */
private function editProduct(Product $product, array $externalProductData) {
    $productData = $this->productDataFactory->createFromProduct($product);
    $this->fillProductData($productData, $externalProductData); // will be implemented in next step

    $this->productFacade->edit($product->getId(), $productData);
}
```

#### 3.5 - Implement `ImportProductsCronModule::fillProductData()`

Finally, we can implement the private method for filling data object
[`ProductData`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductData.php) with external source data.

```php
// src/Model/Product/ImportProductsCronModule.php

// ...

use App\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

// ...

const DOMAIN_ID = 1;
const LOCALE = 'en';
const PRICING_GROUP_ID = 1;

// ...

/**
 * @var \App\Model\Pricing\Vat\VatFacade
 */
private $vatFacade;

// ...

public function __construct(
    // ...

    VatFacade $vatFacade
) {
    $this->vatFacade = $vatFacade;
}


/**
 * @param \App\Model\Product\ProductData $productData
 * @param array $externalProductData
 */
private function fillProductData(ProductData $productData, array $externalProductData)
{
    $productData->name[self::LOCALE] = $externalProductData['name'];
    $productData->manualInputPricesByPricingGroupId[self::PRICING_GROUP_ID] = Money::create($externalProductData['price_without_vat']);
    $productData->vat = $this->vatFacade->getVatByPercent($externalProductData['vat_percent']); // will be implemented in next step
    $productData->ean = $externalProductData['ean'];
    $productData->descriptions[self::DOMAIN_ID] = $externalProductData['description'];
    $productData->usingStock = true;
    $productData->stockQuantity = $externalProductData['stock_quantity'];
    $productData->extId = $externalProductData['id'];
}
```

!!! note
    In order to be able to use stock quantity, we must enable it by setting the `$usingStock` attribute to `true`.

!!! note
    Data from external source contain only integer value for vat percent information but we need [`Vat`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/Vat.php) object
    in [`ProductData`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Product/ProductData.php).
    So we will extend [`VatRepository`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/VatRepository.php)
    and [`VatFacade`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/VatFacade.php) and implement appropriate methods.

!!! tip
    `Money::create()` can be used only for integers and numeric strings.
    If you use floats in your `$externalProductData` array you should always use `Money::createFromFloat()` and specify scale explicitly.
    Read more about monetary values in [How to Work with Money](../model/how-to-work-with-money.md).

#### 3.6 - Extend [`VatRepository`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/VatRepository.php) and implement method `getVatByPercent()` in order to load [`Vat`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/Vat.php) by percent

```php
// src/Model/Pricing/Vat/VatRepository.php

namespace App\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository as BaseVatRepository;

class VatRepository extends BaseVatRepository
{
    /**
     * @param int $percent
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVatByPercent($percent)
    {
        $vat = $this->getVatRepository()->findOneBy(['percent' => $percent]);

        if ($vat === null) {
            throw new \Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException('Vat with ' . $percent . '% not found.');
        }

        return $vat;
    }
}
```

Add information about the class extension into the container configuration in [`services.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services.yaml)

```yaml
Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository: '@App\Model\Pricing\Vat\VatRepository'
```

!!! danger
    The method throws an exception when [`Vat`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/Vat.php) object is not found by given percent value.
    Do not forget to handle it (e.g. skip the product data processing and log the exception).

#### 3.7 - Extend [`VatFacade`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/VatFacade.php) and implement method `getVatByPercent()` in it

```php
// src/Model/Pricing/Vat/VatFacade.php

namespace App\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade as BaseVatFacade;

class VatFacade extends BaseVatFacade
{
    /**
     * @var \App\Model\Pricing\Vat\VatRepository
     */
    protected $vatRepository;

    /**
     * @param int $percent
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVatByPercent($percent)
    {
        return $this->vatRepository->getVatByPercent($percent);
    }
}
```

!!! tip
    You should overwrite `protected $vatRepository` annotation so IDE knows that you are using the extended `VatRepository`

Add information about the class extension into the container configuration in [`services.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services.yaml).
To [make the service public in TEST environment](../introduction/faq-and-common-issues.md#what-is-the-configuration-file-services_testyml-good-for), you need to add the same line into [`services_test.yml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services_test.yml) as well.

```yaml
Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade: '@App\Model\Pricing\Vat\VatFacade'
```

### Step 4 - Everything is done, you can run it now

#### 4.1 Run `php phing cron-list` in console

As an output, you will get a list of all available cron modules.

```text
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Component\Error\ErrorPageCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Cart\Item\DeleteOldCartsCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Feed\DailyFeedCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Feed\HourlyFeedCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDeletionCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Product\ProductSearchExport\ProductSearchExportCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Product\ProductVisibilityImmediateCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Product\ProductVisibilityMidnightCronModule"
php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Sitemap\SitemapCronModule"
php bin/console shopsys:cron --module="Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryCronModule"
php bin/console shopsys:cron --module="App\Model\Product\ImportProductsCronModule"
```

#### 4.2 - Find your module and run appropriate console command

```sh
php bin/console shopsys:cron --module="App\Model\Product\ImportProductsCronModule"
```

!!! hint
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md).

## Best practices
- Validate all incoming data before putting them into data objects.
    - You will avoid SQL errors (e.g. when incoming text is longer than database column allows).
- Transfer modifications only (if possible).
    - Persist the last successful transfer start datetime so next time you can import changes done afterward.
    - It means less data to process.
- Transfer deleted entities too.
    - It is often omitted, but it is necessary if you want to keep consistency between your application database and the external source.
- Use database transactions.
    - Use `EntityManager` methods `beginTransaction()`, `commit()` and `rollback()`.
    - Do not forget to clear identity map when doing a rollback (so even entity modifications are reverted).
    - [`IteratedCronModuleInterface`](https://github.com/shopsys/shopsys/blob/master/packages/plugin-interface/src/Cron/IteratedCronModuleInterface.php) offers a way to implement longer processes that can span over many iterations.
- Disable entity manager SQL logging (use [`SqlLoggerFacade::temporarilyDisableLogging()`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Component/Doctrine/SqlLoggerFacade.php)).
    - By default, every executed SQL query is logged and that slows down the process and consumes a lot of memory when there are many iterations.
    - Do not forget to re-enable the logging after the processing is done (use [`SqlLoggerFacade::reenableLogging()`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Component/Doctrine/SqlLoggerFacade.php)).
- Logging of some key functions might be helpful for future debugging
but it is not a good idea to log "everything". Too many information in logs might be counterproductive.
- Clear entity manager identity map once in a while, because `EntityManager::flush()` searches
for changes in all mapped entities and after time it consumes a huge amount of resources to persist a single entity.
    - Call `EntityManager::flush()` with parameter (i.e. entity or array of entities you want to flush) anytime it is possible.  
     **Warning: Flushing is not cascade operation, i.e. when you flush entity that contains any other entities (e.g. translations),
    these are not flushed automatically. You should not forget to flush them as well.**
    - You should load any entity again after clearing identity map because any attempt to flush the old one will result in an exception.
- Use streamed input for XML and JSON.
    - So you do not load huge files at once (can lead to memory overflow).
- Store external source credentials in `config/parameters.yml`.
    - Storing credentials in local configuration instead of hard-coding them in source code prevents from accidental corrupting of production data.
- Restrict editing of the transferred fields in administration.
    - At least, mark them as transferred to avoid confusion when an administrator changes the field value and then data import overrides the value.
- Transfer overview in administration can be very useful for both administrator and developer of an e-shop.
    - It is handy to know which transfers are currently in progress, which are scheduled, which failed etc.
- Be careful with the order of your data transfers.
    - For example, products have an association with their categories,
    so first you want to transfer products and then their relation to categories.

## Pitfalls
- External data source often sends null values as empty strings, so be very careful with validating the incoming data. (`$value !== null` might not be sufficient in that case).
- When processing a large amount of data, you can use native queries instead of using ORM, which might save you a certain amount of SQL queries.
On the other hand, you have to handle all related logic manually then.
- It is necessary to mark transferred products for recalculations (e.g. price, visibility, ...).
- In the current state, e-shop can not handle transferring huge amount (thousands) of categories.
    - Categories use "nested set" structure (`@Gedmo\Tree(type="nested")`) which produces many SQL queries.

## Conclusion

Now you know how to implement simple data transfer to your e-shop from an external source.
You know why you should persist external ID and how to decide whether create new entities or update existing ones.
You learned about Shopsys Framework cron modules, how to create and run them. You also know how to get desired objects based on external data
(e.g. [`Vat`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Model/Pricing/Vat/Vat.php) object based on vat percent).
You are familiar with best practices for implementing data transfers,
what pitfalls you can encounter with and what are the ways of dealing with them.

