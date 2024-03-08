# Product Recalculations (price and visibility recalculation and export to Elasticsearch)

[TOC]

Product recalculations in terms of this article mean recalculations of the product visibility, selling denial, and export to Elasticsearch.
In Shopsys Platform, these recalculations are done asynchronously, which means that when a product is changed, the recalculations are not done immediately, but rather a message is dispatched to the message broker.
This approach has been used since `14.0` version onwards instead of cron modules and allows, among other benefits, also to horizontally scale the recalculations.
For a larger catalog, several consumers may be run to handle the recalculations and drastically reduce the time necessary to present the changes of products on the Storefront.

## Dispatch recalculations message

When you need to recalculate visibility of product(s), you should use the `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher` service.

```php
class MyService
{
    public function __construct(
        private readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    public function myMethod()
    {
        // ... some work

        $this->productRecalculationDispatcher->dispatchProductIds([1, 2, 3]);
    }
}
```

This method can be safely called in any context (console, cron, request), and the recalculation will be done properly.

Also, it's not necessary to think about the variants – the dispatcher takes care of recalculating the whole group of variants.  
When, for example, the main variant is changed, it's enough to dispatch a message for the main variant (see [Recalculation of variants](#recalculation-of-variants)).

When you need to recalculate visibility of all products, you should use the `Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher::dispatchAllProducts()` method.

This method dispatches the special message "dispatch all" to the message broker, and this message is then handled by the async handler, which takes care of dispatching all product IDs to recalculation.
That way, we may dispatch all products to recalculation during request without worrying about the size of the catalog – it's not necessary to load all products (nor their IDs) from the database, and the user interface is not blocked by this operation.

## Dispatch recalculation message when indirect change is made

Sometimes it is necessary to trigger recalculation for product when some indirect change is made.
For example, when a category is deleted, when the parameter translation of a product is changed, etc.

For this situation, the `\Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAffectedProductsSubscriber` subscriber is used.

Whenever a change is made in the entity that affects products, the appropriate event is dispatched (e.g. `Shopsys\FrameworkBundle\Model\Category\CategoryEvent::UPDATE`).
The `DispatchAffectedProductsSubscriber` subscriber listens to these events and dispatches the appropriate message for the message broker.

## Recalculation of variants

Because variants are tightly coupled with the main variant, the recalculations have to be always done for the whole group (variants + main variant).
But it's no longer necessary to take this into account in a custom code.
When, for example, only a single variant is changed, it's enough to dispatch a message for this variant.
Recalculation will be done automatically for the whole group to be sure all products are in a proper state (similarly for the main variant).

## Recalculation cron module

Cron module `ProductRecalculationCronModule` is configured to run every day at midnight and dispatches all products to recalculation.
This ensures the product is recalculated at the start of a new day to cover scenarios when it is supposed to be visible or hidden from a particular date (see `Product::$sellingFrom` and `$sellingTo` properties). Moreover, it is a safety net to make sure all products are recalculated at least once a day to prevent inconsistencies in the catalog due to possible mistakes in the code.

```yaml
Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductRecalculationCronModule:
    tags:
        - {
              name: shopsys.cron,
              hours: '0',
              minutes: '0',
              instanceName: products,
              readableName: 'Dispatches all products to be recalculated and exported',
          }
```

You may disable this cron module if it doesn't suit your needs.

## Handle recalculations in tests

In functional tests, you sometime need to trigger recalculations to be able to test some use-case.
For example, when you need to test the change of a product visibility, you need to recalculate it after the change is made, so the data returned from GraphQL are accurate.

You should use the `Tests\App\Test\WebTestCase::handleDispatchedRecalculationMessages()` method to process all dispatched recalculation messages.

```php
public function testRefreshProductsVisibilityVisibleVariants()
{
    /** @var \App\Model\Product\Product $variant1 */
    $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');

    $variant1productData = $this->productDataFactory->createFromProduct($variant1);
    $variant1productData->hidden = true;
    $this->productFacade->edit($variant1->getId(), $variant1productData);

    // recalculations are processed here
    $this->handleDispatchedRecalculationMessages();

    /** @var \App\Model\Product\Product $variant1 */
    $variant1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '53');
    /** @var \App\Model\Product\Product $variant2 */
    $variant2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '54');
    /** @var \App\Model\Product\Product $mainVariant */
    $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69');

    $this->assertFalse($variant1->isVisible(), 'first variant should be invisible');
    $this->assertTrue($variant2->isVisible(), 'second variant should be visible');
    $this->assertTrue($mainVariant->isVisible(), 'main product should be visible');
}
```

In tests, the real queue is not used, the `handleDispatchedRecalculationMessages()` method only processes the messages dispatched during the test.
That way we are sure the code works – the message is truly dispatched, thus the product is recalculated – everything with the same code used in real life.

!!! important

    Calling `handleDispatchedRecalculationMessages()` method creates a snapshot in Elasticsearch before any changes are exported and restores it afterward.<br>
    Tests are not dependent on the changes made or the order of run.

## Recalculation scope

In many occasions, to optimize the application, you might need to restrict the scope of a product recalculation and elastic export.
Sometimes you do not need to recalculate visibility or selling denied, or you need to export just particular fields into Elasticsearch.
For example, when launching a new domain, you need to export just the product URLs into Elasticsearch which takes significantly less time than the full recalculation and export.
On the other hand, it might be hard to keep in mind all the dependencies (e.g. you need to know you need to recalculate visibility after you change `Product::$sellingFrom`,
or you need to keep in mind that with a change of product URL, you always need to export `hreflang_links` into Elasticsearch as well, etc.).

For this purpose, recalculation scopes are defined as an associative array in the `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfigFacade` class.
The scopes are represented by instances of the `Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeRule` class and indexed by their names.
Each scope rule defines which fields should be exported to Elasticsearch together (`$productExportFields` property) and whether some actions (recalculations) should be done before the export (`$productExportPreconditions` property).

You can use `./bin/console shopsys:list:export-scopes` command to list and examine all the available scopes.

The scope usage can be seen in action e.g. in `Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAffectedProductsSubscriber` class:

```php
public function dispatchAffectedByBrand(BrandEvent $brandEvent): void
{
    $productIds = $this->affectedProductsFacade->getProductIdsWithBrand($brandEvent->getBrand());

    $this->productRecalculationDispatcher->dispatchProductIds(
        $productIds,
        ProductRecalculationPriorityEnum::REGULAR,
        [ProductExportScopeConfig::SCOPE_BRAND],
    );
}
```

Thanks to the usage of `SCOPE_BRAND` here, no visibility or selling denied recalculations are done after a brand is updated, only the brand-related fields (brand ID, name, and URL) are exported to Elasticsearch for the affected products.

There are several methods that allow you to modify the scopes configuration further to suit your project needs.

-   `addExportFieldsToExistingScopeRule()`
-   `addNewExportScopeRule()`
-   `overwriteExportScopeRule()`

You can use the methods in the overridden `App\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig::loadProductExportScopeRules()`.

!!! note

    The export fields restriction is ignored in situations when the product is not present in Elasticsearch (e.g. it was not exported yet) and visibility recalculation needs to be done.
    In such cases, all the product fields are always exported to Elasticsearch to ensure no data are missing for the product.

## Invoke recalculations manually

It's possible to invoke recalculations manually with the `./bin/console shopsys:dispatch:recalculations` command.

This command accepts ids of products, that should be dispatched, or `--all` option to dispatch all products.
You can also define the priority and/or scopes of the recalculations using `--priority`, and/or `--scope` options.

```bash
# dispatch products with ids 1, 2 and 3
./bin/console shopsys:dispatch:recalculations 1 2 3

# dispatch all products
./bin/console shopsys:dispatch:recalculations --all

# dispatch product with id 22 into the high priority queue
./bin/console shopsys:dispatch:recalculations 22 --priority=high

# dispatch all products with the "product_selling_denied_scope" scope
./bin/console shopsys:dispatch:recalculations --all --scope=product_selling_denied_scope
```
