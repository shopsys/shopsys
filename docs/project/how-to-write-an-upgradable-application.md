# How to write an upgradable application

This article describes a list of useful tips on how to write your application to make the upgrade to the new version of Shopsys Platform as smooth as possible.

[TOC]

## Constructor overrides

From time to time, you need to override functionality completely so you don't call the parent method at all.
This is especially dangerous in constructors as constructors don't need to be the same in parent–child class.
We leverage this to be able to introduce a new optional dependency of the class.

If you don't call `parent:__construct()` in your code, and we add initialization of some property, that initialization will not be called in your code.
It's really useful to add a comment for your future self that the parent is not called intentionally and check for those comments while upgrading.

For example, when we make this change in the new version

```diff
namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterData {

    // ...

    public function __construct()
    {
        $this->name = [];
        $this->visible = false;
+       $this->promoted = false;
    }
```

You may encounter problems with the following code (notice the `promoted` property will not be set)

```php
namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData as BaseParameterData;

class ParameterData extends BaseParameterData {

    // ...

    public function __construct()
    {
        // parent::__construct() not called intentionally to avoid setting parameter visibility 
        $this->name = [];
    }
```

Adding a simple comment like the one above helps you identify those places and quickly check them.
Also, you may be sure this is intentional and not a bug.

## Reported bug

Sometimes, you may find a bug or request a feature in Shopsys Platform,
but until the reported (thanks for that!) issue/request is processed and a new version is released, you need to fix it in your code.

Then, it's really useful to make a notice in your code so you can quickly decide in the future whether some parts of your code can be safely deleted as the original issue is already resolved.

```php
    /**
     * @param array $matches
     * @param string|null $locale
     * @return string
     */
    protected function replace(array $matches, ?string $locale): string
    {
        // ...

        // $productClassName is required to be set manually until https://github.com/shopsys/shopsys/issues/1693 is resolved
        $productClassName = 'Shopsys\FrameworkBundle\Model\Product\Product';
        $imageViews = $this->imageViewFacade->getForEntityIds($productClassName, $this->getIdsForProducts($products));
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        // ...
```

## Deprecations

After the upgrade (or before upgrading to another version), you may want to check for deprecations (see them in the Symfony debug toolbar) and resolve those reported from the Shopsys namespace.
It will make your future upgrades easier.

```text
// example
User Deprecated: The Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig::setEntityNameResolver() method is deprecated and will be removed in the next major. Use the constructor injection instead.
```

## Adding New Properties to Objects

When you need to add new typed properties to already existing objects, you may find it useful to initialize those properties with setters instead of overriding the constructor.

For future development, using getter/setter instead of public property is better otherwise, when you try to read from uninitialized public property in Twig, you get a misleading error notice:

```text
Neither the property "nonSellingPrice" nor one of the methods "nonSellingPrice()", "getnonSellingPrice()"/"isnonSellingPrice()"/"hasnonSellingPrice()" or "__call()" exist
and have public access in class "App\Model\Product\Detail\ProductDetailView"
```

Thanks to the usage of a getter, you see the actual error, so you are immediately aware of the problem:
```text
Typed property App\Model\Product\Detail\ProductDetailView::$nonSellingPrice must not be accessed before initialization
```
