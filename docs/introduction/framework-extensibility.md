# Framework Extensibility

This article summarizes the current possibilities of the framework extension,
provides a list of customizations that are not achievable now but are planned to be enabled soon,
as well as a list of customizations that are not (and will not be) possible at all.

## What is achievable easily
* [Extending an entity](/docs/extensibility/entity-extension.md)
    * [Adding a new attribute](/docs/cookbook/adding-new-attribute-to-an-entity.md)
    * *Note: There are some limitations when extending OrderItem, for more see [the documentation](/docs/extensibility/entity-extension.md#orderitem)*
* The administration can be extended by:
    * [Adding a new administration page](/docs/cookbook/adding-a-new-administration-page.md) along with the side menu and breadcrumbs
    * [Extending particular forms](/docs/extensibility/form-extension.md) without the need of the template overriding
* [Customizing database migrations](/docs/introduction/database-migrations.md)
    * adding a new migration as well as skipping and reordering the existing ones
* Configuring the smoke tests (see [`RouteConfigCustomization`](/project-base/tests/ShopBundle/Smoke/Http/RouteConfigCustomization.php) class)
    * *Note: This is now achievable as the configuration class is located in the open box project-base.
    However, that makes the upgrading of the component harder so the configuration is planned to be re-worked.*
* [Implementing custom product feed or modifying an existing one](/docs/model/product-feeds.md)
* [Implementing a basic data import](/docs/cookbook/basic-data-import.md) to import data to you e-shop from an external source
    * adding a new cron module and configuring it
* [Extending the application using standard Symfony techniques](https://symfony.com/doc/current/bundles/override.html)
    * e.g. overriding Twig templates, routes, services, ...
* [Adding a new advert position](/docs/cookbook/adding-a-new-advert-position.md) to be used in the administration section *Marketing > Advertising system*
* open-box modifications in `project-base`
    * e.g. adding new entities, changing the FE design, customization of FE javascripts, adding new FE pages (routes and controllers), ...
* [Hiding the existing features and functionality](https://github.com/shopsys/demoshop/pull/13)
* adding a new javascript into admin
    * add the new javascript files into the directory `src/Shopsys/ShopBundle/Resources/scripts/custom_admin` and they will be loaded automatically

## What is achievable with additional effort

* Extending factories and controllers - [see commit in demoshop](https://github.com/shopsys/demoshop/commit/898d111879aef40196f79ac763373560f44aef59#diff-1b3bd68670cd376165cdc6cfc634f24f)
* Adding form option into existing form - [see commit in demoshop](https://github.com/shopsys/demoshop/commit/898d111879aef40196f79ac763373560f44aef59#diff-3293b000b06ad6c0280341584c4d661d)
* Extending administration form theme - [see commit in demoshop](https://github.com/shopsys/demoshop/commit/d0e0eaaa2eeac5e1c90d8a29be5c827c4a067b9f)
* Changing an entity association - [see commit in demoshop](https://github.com/shopsys/demoshop/commit/9931083ea37ad611568e32bc1a9c8cf203401809) [*and actual association change*](https://github.com/shopsys/demoshop/commit/f3884368289da4b7c5eb1cee3078c9ec69c933dc)
    * this change is complicated and potentially dangerous

## Which issues are going to be addressed soon
* Extending data fixtures (including performance data fixtures)
* Extending data grids in the administration
* Extending all forms in the administration without the need of the template overriding
* Extending classes like Repositories without the need for changing the project-base tests

## What is not supported
* Removing an attribute from a framework entity
* Changing a data type of an entity attribute
* Removing existing entities and features
* Extending [the `Money` class](/docs/model/how-to-work-with-money.md) and closely related classes (eg. `MoneyType`)

## Examples of implemented features on the [Demoshop repository](https://github.com/shopsys/demoshop)
* [Shipping method with pickup places](https://github.com/shopsys/demoshop/pull/6)
    * new shipping method Zasilkovna
    * pick up places are downloaded by cron
    * order process change
    * details in a [issue description](https://github.com/shopsys/demoshop/issues/3)
* [Product attribute "condition"](https://github.com/shopsys/demoshop/pull/7)
    * product entity extension
    * administration form extension
    * frontend product change
    * google feed change
    * detailed info in a [issue description](https://github.com/shopsys/demoshop/issues/4)
* [Second description of a category](https://github.com/shopsys/demoshop/pull/8)
    * category entity extension
    * administration form extension
        * new multidomain
    * frontend product list change
    * detailed info in a [issue description](https://github.com/shopsys/demoshop/issues/5)
* [Twig templates cache](https://github.com/shopsys/demoshop/pull/9)
    * performance improved by ~15%
    * cache is invalidated every 5 minutes
* [Hidden the functionality of the flags](https://github.com/shopsys/demoshop/pull/13)
    * hidden functionality in administration
    * hidden functionality in frontend
    * flags do not affect shop at all
* [Company account with multiple users](https://github.com/shopsys/demoshop/pull/15)
    * group user accounts under one company account
    * separate users login credentials
    * share company attributes
    * change association from 1:1 to 1:N

## Making the static analysis understand the extended code
### Problem 1
When extending framework classes, it may happen that tools for static analysis (e.g. PHPStan, PHPStorm) will not understand your code properly.
Imagine this situation:

- You have a controller that is dependent on a framework service:
```php
namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     */
    public function __construct(ProductFacade $productFacade)
    {
        $this->productFacade = $productFacade;
    }
}
```
- In your project, you extend the framework's `ProductFacade` service:
```php
namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

class ProductFacade extends BaseProductFacade
{
    public function myCustomAwesomeFunction()
    {
        return 42;
    }
}
```
- You register your extension in DI services configuration and thanks to that, your class is used in `ProductController` instead of the one from `FrameworkBundle`, so far so good:
```yaml
Shopsys\FrameworkBundle\Model\Product\ProductFacade: '@Shopsys\ShopBundle\Model\Product\ProductFacade'
```
**However, when you want to use your `myCustomAwesomeFunction()` in `ProductController`, the static analysis is not aware of that function.**
#### Solution
To fix this, you need to change the annotations properly:
```diff
class ProductController
{
      /**
-      * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
+      * @var \Shopsys\ShopBundle\Model\Product\ProductFacade
       */
      protected $productFacade;

      /**
-      * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
+      * @param \Shopsys\ShopBundle\Model\Product\ProductFacade $productFacade
       */
      public function __construct(ProductFacade $productFacade)
      {
          $this->productFacade = $productFacade;
      }
}
```
**Luckily, you do need to fix the annotations manually, there is the [Phing target `fix-annotations`](./console-commands-for-application-management-phing-targets.md#fix-annotations), that handles everything for you.**

### Problem 2
There might be yet another problem with static analysis when extending framework classes.
Imagine the following situation:

- In framework, there is `ProductFacade` that has `ProductRepository` property
```php
namespace Shopsys\FrameworkBundle\Model\Product;

class ProductFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    public function getProductRepository()
    {
        retrun $this->productRepository;
    }
}
```
- In your project, you extend `ProductRepository` and `ProductFacade` as well.
- Then, in your extended facade, you want to access the repository (generally speaking, you want to access the parent's property that has a type that is extended in your project, or you want to access a method that returns a type that is already extended):
```php
namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

class ProductFacade extends BaseProductFacade
{
    public function myCustomAwesomeFunction()
    {
        $this->productRepository; // static analysis thinks this is of type \Shopsys\FrameworkBundle\Model\Product\ProductRepository
        $this->getProductRepository(); // static analysis thinks this is of type \Shopsys\FrameworkBundle\Model\Product\ProductRepository
    }
}
```
- **Once again, static analysis is not aware of the extension.**
#### Solution
To fix this, you don't need to override the method or property, you just need to add proper `@method` and `@property` annotations to your class:
```diff
namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade as BaseProductFacade;

+ /**
+  * @method \Shopsys\ShopBundle\Model\Product\ProductRepository getProductRepository()
+  * @property \Shopsys\ShopBundle\Model\Product\ProductRepository $productRepository
+  */
  class ProductFacade extends BaseProductFacade
  {
```
**Even this scenario is covered by `fix-annotations` phing target.**

### Problem 3
There is one kind of problem that is not fixed automatically and needs to be addressed manually.
Shopsys Framework uses a kind of magic for working with extended entities (see [`EntityNameResolver` class](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Component/EntityExtension/EntityNameResolver.php)),
and static analysis tools are not aware of that fact.
Imagine the following situation:
- You have extended `Product` entity in your project
- In the framework, there is `ProductFacade` class that is not extended in your project, and it has a method that returns instances of `Product` entity (in fact, it returns instances of your child `Product` entity thanks to the mentioned `EntityNameResolver` magic).
```php
namespace Shopsys\FrameworkBundle\Model\Product;

// the class has no extension in your project
class ProductFacade
{

    /**
     * This class is not extended in the project either
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getById($id)
    {
        // despite the annotation, extended Product entity from your project is returned
        return $this->productRepository->getById($id);
    }
}
```
- You have a controller that is dependent on the framework service:
```php
namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    public function __construct(ProductFacade $productFacade)
    {
        return $this->productFacade = $productFacade;
    }

    /**
     * @param int $id
     * Your Product instance is returned indeed, but static analysis is confused
     * @return \Shopsys\ShopBundle\Model\Product\Product
     */
    private function myAwesomeMethod($id)
    {
        return $this->productFacade->getById($id);
    }
}
```
**In such a case, static analysis does not understand that the extended `Product` entity is returned.**
#### Solution
This needs to be fixed manually using a local variable with an inline annotation:
```diff
private function myAwesomeMethod($id)
{
+    /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
+    $product = $this->productFacade->getById($id);


-    return $this->productFacade->getById($id);
+    return $product;
}
```

As a workaround for this, you can create an empty class extending the one from the framework, register the extension in your `services.yml`, and then use `php phing fix-annotations` to fix appropriate annotations for you.

Which way to go really depends on your situation. If you are likely to extend the given framework class sooner or later, or the same problem with the class is reported in many places, it would be better to create the empty extended class right away.
Otherwise, it might be better just extracting and annotating the variable manually (like in [this commit in monorepo](https://github.com/shopsys/shopsys/commit/efd008b8d))
as it is quicker and you can avoid having an unused empty class in your project.

### Tip
If you are a fan of an automation and PHPStorm user at the same time, you can simplify things even more and set your IDE to automatically run the phing target every time you e.g. change something in your project.
This can be achieved by setting up a custom "[File watcher](https://www.jetbrains.com/help/phpstorm/using-file-watchers.html)".
