# Memory Cache

If you need to cache some data in your class to prevent multiple requests on the database, you can use class `InMemoryCache`.
`InMemoryCache` saves data into server memory and is cleared every time the `EntityManager::clear()` is called.

## Example of basic usage

```php
class MyService
{
    private const string CACHE_NAMESPACE = 'my_service';

    public __construct(private readonly InMemoryCache $inMemoryCache)
    {
    }

    public function getSomething($id)
    {
        if (!$this->inMemoryCache->hasItem(self::CACHE_NAMESPACE, $id)) {
            $this->inMemoryCache->save(self::CACHE_NAMESPACE, $this->calculateSomething($id), $id);
        }

        return $this->inMemoryCache->getItem(self::CACHE_NAMESPACE, $id);
    }
}
```

## Example of usage with callback

`InMemoryCache` also allows you to use callback as value for saving item to the cache for easier managing of complicated data resolving.

```php
public function getProductParameterValues(Product $product, ?string $locale = null)
{
    return $this->inMemoryCache->getOrSaveValue(
        static::PARAMETER_VALUES_CACHE_NAMESPACE,
        function () use ($product, $locale) {
            $locale = $locale ?? $this->localization->getLocale();

            $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByOrderingPriorityAndName(
                $product,
                $locale,
            );

            foreach ($productParameterValues as $index => $productParameterValue) {
                $parameter = $productParameterValue->getParameter();

                if ($parameter->getName($locale) === null
                    || $productParameterValue->getValue()->getLocale() !== $locale
                ) {
                    unset($productParameterValues[$index]);
                }
            }

            return $productParameterValues;
        },
        $product->getId(),
    );
}
```
