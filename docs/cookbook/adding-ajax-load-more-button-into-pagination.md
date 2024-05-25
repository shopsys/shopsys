# Adding Ajax Load More Button into Pagination

In this cookbook, we will add a paginated brand list, including an ajax "load more" button to a product list page.
After finishing the guide, you will know how to use multiple paginations on one page.

!!! note

    After this change, you will have paginated also `/brands-list/` page (`front_brand_list` route).

## Implementation of Brand Pagination

First, we need to extend `Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository` by creating `BrandRepository.php` in `/src/Model/Product/Brand`, and we add `getPaginationResult` method.

```php
namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository as BaseBrandRepository;

class BrandRepository extends BaseBrandRepository
{
    /**
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginationResult(
        $page,
        $limit
    ) {
        $queryBuilder = $this->getBrandRepository()->createQueryBuilder('b');
        $queryBuilder->orderBy('b.name', 'asc');

        /** @var \Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator $queryPaginator */
        $queryPaginator = new QueryPaginator($queryBuilder);

        return $queryPaginator->getResult($page, $limit);
    }
}
```

Then we will extend `Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade` and add `getPaginatedResult` method.

```php
namespace App\Model\Product\Brand;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade as BaseBrandFacade;

class BrandFacade extends BaseBrandFacade
{
    /**
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedResult(
        $page,
        $limit
    ) {
        $paginationResult = $this->brandRepository->getPaginationResult(
            $page,
            $limit
        );

        return new PaginationResult(
            $paginationResult->getPage(),
            $paginationResult->getPageSize(),
            $paginationResult->getTotalCount(),
            $paginationResult->getResults()
        );
    }
}
```

After we have extended `BrandRepository` and `BrandFacade`, we need to set them to be used instead of the framework classes in our application.
This is done via configuration in `services.yaml`.

```yaml
Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository: '@App\Model\Product\Brand\BrandRepository'

Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade: '@App\Model\Product\Brand\BrandFacade'
```

Next, we will modify the brand list twig template, replacing the whole content and creating a twig template for rendering paging controls and paginated items via ajax, where we move the original content from the brand list template.
We will use `paginator.loadMoreButton(paginationResult, url('front_brand_list'), pageQueryParameter)` twig component that will asynchronously load the next page when the user clicks on its button.
We will also define `pageQueryParameter` variable so it will have a unique name and it will not interfere with other paging components on the same page.

```twig
{# templates/Front/Content/Brand/list.html.twig #}
{% extends 'Front/Layout/layoutWithoutPanel.html.twig' %}

{% block title %}
    {{ 'Brand overview'|trans }}
{% endblock %}

{% block main_content %}
    <div>
        <h1>{{ 'Brand overview'|trans }}</h1>
        {% include 'Front/Content/Brand/ajaxList.html.twig' with {paginationResult: paginationResult} %}
    </div>
{% endblock %}
```

There are two important CSS classes that must be used.

-   `js-list-with-paginator` - element with this class encapsulates paging component
-   `js-list` - fragment from which new items are pulled during an asynchronous call

```twig
{# templates/Front/Content/Brand/ajaxList.html.twig #}
{% import 'Front/Inline/Paginator/paginator.html.twig' as paginator %}
{% set entityName = 'brands'|trans %}
{% set pageQueryParameter = 'brandPage' %}

<div>
    <div class="js-list-with-paginator">
        {{ paginator.paginatorNavigation(paginationResult, entityName, pageQueryParameter) }}
        <ul class='list-images js-list'>
            {% for brand in paginationResult.results %}
                <li class="list-images__item">
                    <a href="{{ url('front_brand_detail', { id: brand.id }) }}" class="list-images__item__block list-images__item__block--with-label">
                        {{ image(brand, { alt: brand.name }) }}
                        <span>{{ brand.name }}</span>
                    </a>
                </li>
            {% endfor %}
        </ul>
        <div class="text-center margin-bottom-20">
            {{ paginator.loadMoreButton(paginationResult, url('front_brand_list'), pageQueryParameter) }}
        </div>
        {{ paginator.paginatorNavigation(paginationResult, entityName, pageQueryParameter) }}
    </div>
</div>
```

After that, we will modify `listAction` method in `BrandController` so `Brand` list page will be paginated, and we will be able to integrate it into another list page with other paginated items.
We will also implement constants for page query parameter `const PAGE_QUERY_PARAMETER = 'brandPage'` and for the count of items on one page `const ITEMS_PER_PAGE = 5;`.

To determine whether the request for the brand list is called from the template, we need to add the dependency on `Symfony\Component\HttpFoundation\RequestStack` into our `BrandController`.

```php
const PAGE_QUERY_PARAMETER = 'brandPage';
const ITEMS_PER_PAGE = 5;

/**
 * @var \Symfony\Component\HttpFoundation\RequestStack
 */
private $requestStack;

/**
 * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
 * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
 */
public function __construct(
    BrandFacade $brandFacade,
    RequestStack $requestStack
) {
    $this->brandFacade = $brandFacade;
    $this->requestStack = $requestStack;
}

/**
 * @param \Symfony\Component\HttpFoundation\Request $request
 */
public function listAction(Request $request)
{
    // check whether the request is called directly via route or via the Twig template
    $isMainRequest = $this->requestStack->getMainRequest() === $request;

    if ($request->isXmlHttpRequest() || !$isMainRequest) {
        $template = 'Front/Content/Brand/ajaxList.html.twig';
    } else {
        $template = 'Front/Content/Brand/list.html.twig';
    }

    $requestPage = $request->get(self::PAGE_QUERY_PARAMETER);
    $page = $requestPage === null ? 1 : (int)$requestPage;

    return $this->render($template, [
        'paginationResult' => $this->brandFacade->getPaginatedResult($page, self::ITEMS_PER_PAGE),
    ]);
}
```

### Customizing the "load more" button text

By default, the "load more" button displays general text - "Load next X item(s)".
The option `buttonTextCallback` is available for `Shopsys.AjaxMoreLoader` javascript component that you can use to customize the displayed text to fit your use case.
You can see the usage of the option in [`productList.js`]({{github.link}}/project-base/assets/js/frontend/product/productList.js).

## Integration of Paginated Brand List

We have implemented a paginated `Brand` list page that can also be loaded from asynchronous calls.
We can integrate it into another `Product` list page that is also paginated with the page query parameter `page`.
We only need to modify the template for the `Product` page.
We will add twig code into `main_content` block.

```twig
{# templates/Front/Content/Product/list.html.twig #}
{{ render(controller('App\\Controller\\Front\\BrandController::listAction')) }}
```

## Conclusion

Customers can see 2 paginated lists with buttons for loading items from the next pages on each `Product` list page.
Since there are unique page query parameters, paginated lists can display different page indexes after the browser is loaded with these page query parameters.
