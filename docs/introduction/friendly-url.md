# Friendly URL

Shopsys Framework comes with the implementation to support custom URLs for entities or other use you might need.
By default, there are custom URLs implemented for product detail, product list, article detail and brand detail pages.
Thanks to this functionality you can set your own URL or set of URLs to these entities.
This functionality is provided by `FriendlyUrlDataProviderInterface` implementations, e.g. `ProductDetailFriendlyUrlDataProvider`.
Such classes are automatically registered in `FriendlyUrlDataProviderRegistry`.
The rest of the work is done automatically and URLs provided by these providers are now accessible via browser.

## How to create new `FriendlyUrlDataProvider`

- Create new class with name ending with `FriendlyUrlDataProvider` implementing `FriendlyUrlDataProviderInterface` interface

- This interface requires you to implement two methods *(see [ProductDetailFriendlyUrlDataProvider](https://github.com/shopsys/shopsys/blob/9.0/packages/framework/src/Model/Product/ProductDetailFriendlyUrlDataProvider.php) class as an example of the implementation)*:

    - `getFriendlyUrlData` method that generates `FriendlyUrlData` for all your entities

    - `getRouteName` method returns name of route that you have to declare in `routing_friendly_url.yml` file like:

```yaml
front_<entity_name>_detail:
    path: friendly-url
    defaults: { _controller: App\Controller\Front\<entity_name>Controller:detailAction }
```

this will route all URLs you have provided in `getFriendlyUrlData` method to `<entity_name>Controller::detailAction` method and provide you with ID of entity matching that URL
*(see [Symfony Documentation](https://symfony.com/doc/3.4/controller.html) to learn how to create new `Controller`)*

- update your `<entity_name>Facade`:

    - in `create` and `edit` methods, after calling `$this->em->flush()`, add similar code like this:

```php
// third parameter is an array of names indexed by locale that will be used for URL generation (e.g. 'cs' => 'Televize', 'en' => 'Television')
$this->friendlyUrlFacade->createFriendlyUrls('front_<entity_name>_detail', $entity->getId(), $entity->getNames());

```

this way you will have always generated friendly URLs based on your entity name and if the name of the entity will be changed, the old URLs will now redirect to your new URLs

- run `php phing friendly-urls-generate` to generate new URLs

- visit some of provided URLs and check if everything works fine

!!! note
    You can use [`UrlListType` in your forms](./using-form-types.md#urllisttype) to edit friendly URLs of existing entities.  
    If you're interested, you can take a look at the processing of `Article` entity (see `ArticleFacade`, `ArticleData::$urls`, `ArticleDataFactory` and `ArticleFormType`), which allows for this functionality.
