# Adding an Attribute to Product Export

This short cookbook describes the steps that you need to do when you want to add a new attribute to the product export method of the [backend API](../../backend-api/introduction-to-backend-api.md).

Let's say you have added `extId` attribute to `Product` entity following [the cookbook](../../cookbook/adding-new-attribute-to-an-entity.md) and you want to include the attribute in the export as well.

## 1. Implement your own product transformer by extending `ApiProductTransformer`
[`ApiProductTransformer`](https://github.com/shopsys/shopsys/blob/master/packages/backend-api/src/Controller/V1/Product/ApiProductTransformer.php) is responsible for providing array of product data to backend API controllers.
You need to add your attribute to the array that is returned in `transform()` method.
```php
namespace App\Controller\Api\V1\Product;

use Shopsys\BackendApiBundle\Controller\V1\Product\ApiProductTransformer as BaseApiProductTransformer;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ApiProductTransformer extends BaseApiProductTransformer
{
    /**
     * @param \App\Model\Product\Product $product
     * @return array
     */
    public function transform(Product $product): array
    {
        $productApiData = parent::transform($product);
        $productApiData['extId'] = $product->getExtId();

        return $productApiData;
    }
}
```

## 2. Add information about the class extension into the container configuration in [`services.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/services.yaml)
```yaml
Shopsys\BackendApiBundle\Controller\V1\Product\ApiProductTransformer: '@App\Controller\Api\V1\Product\ApiProductTransformer'
```

## Conclusion
Now, the `extId` attribute is included in the product export.
You can [access the `/api/v1/products` endpoint](../../backend-api/introduction-to-backend-api.md#try-it) and see it for yourself.
