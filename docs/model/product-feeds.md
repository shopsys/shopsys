# Product Feeds

Product feeds are a way to periodically export information about your products for product search engines such as [Google Shopping](https://www.google.com/shopping).

To allow easy installation and removal of product feeds, they are implemented in form of plugins ([see list of current implementations](https://github.com/search?q=topic%3Aproduct-feed+org%3Ashopsys)).

## Where are the feeds?

The exported files contain a random token generated during the application build, so their URL address is not easily guessed.
You can see all installed product feeds along with the URLs of their export in the administration section _Marketing > XML Feeds_.

## When are they exported?

Product feeds are usually exported using Cron modules.
The Cron modules are already implemented and registered, all that's needed is to run the [`cron` phing target](../introduction/console-commands-for-application-management-phing-targets.md#cron) every 5 minutes (can be changed in parameters) on your server and Shopsys Platform takes care of the rest.
They can be also generated manually in the administration section _Marketing > XML Feeds_, if you're logged in as _superadministrator_.

Each feed definition in `services.yaml` includes hours and minutes in cron format, when it should be generated.

For example:

```yaml
Shopsys\ProductFeed\GoogleBundle\GoogleFeed:
    tags:
        - { name: shopsys.product_feed, hours: '1', minutes: '0' }
```

Google feed will be generated every day at 1:00 AM.

You can also set it like this:

```yaml
Shopsys\ProductFeed\GoogleBundle\GoogleFeed:
    tags:
        - { name: shopsys.product_feed, hours: '*/4', minutes: '0' }
```

In such a case, this feed will be generated every four hours.

Feeds have their default times set in their own `services.yaml` files, but can be easily changed in your project's `feed.yaml` file.
You only need to copy service definition from feed `services.yaml` file and change the hours and minutes to expected ones.

## How to run feed outside scheduled time?

If you want to run feed outside scheduled time, you can use the `php bin/console shopsys:feed-schedule` command with `--feed-name` argument to schedule one feed or `--all` argument to schedule all feeds and then run `php bin/console shopsys:cron --module="Shopsys\FrameworkBundle\Model\Feed\FeedCronModule" --instance-name=export` to generate the feeds.

## How to limit a product feed to a specific domain?

If you want to limit a product feed to a specific domain, you can use the `domain_ids` parameter in the feed's service definition.
Copy the service definition from the feed's `services.yaml` file and add the `domain_ids` parameter with the IDs of the domains you want to limit the feed to in your projects `feed.yaml` file.

For example, if you want to limit Google Feed to first and third domain, you will add this to your `feed.yaml` file.

```yaml
Shopsys\ProductFeed\GoogleBundle\GoogleFeed:
    tags:
        - { name: shopsys.product_feed, hours: '1', minutes: '0', domain_ids: '1,3' }
```

## How to implement a custom product feed?

The heart of a product feed plugin is a service implementing the [`FeedInterface`]({{github.link}}/packages/framework/src/Model/Feed/FeedInterface.php) that is tagged in a DI container with `shopsys.feed` tag.
Tags `hours` and `minutes` are mandatory and define when the feed should be generated.

The annotations in the feed interfaces ([`FeedInterface`]({{github.link}}/packages/framework/src/Model/Feed/FeedInterface.php), [`FeedInfoInterface`]({{github.link}}/packages/framework/src/Model/Feed/FeedInfoInterface.php) and [`FeedItemInterface`]({{github.link}}/packages/framework/src/Model/Feed/FeedItemInterface.php)) should explain a lot.
When in doubt, you can take a look at the [already implemented product feeds](https://github.com/search?q=topic%3Aproduct-feed+org%3Ashopsys) for inspiration.

## How to extend an existing product feed?

[Already existing product feed modules](https://github.com/search?q=topic%3Aproduct-feed+org%3Ashopsys) can be customized in several ways:

-   to use a different Twig template you can either [override the template](https://symfony.com/doc/3.3/templating/overriding.html)
    or you can extend the service tagged as `shopsys.feed` and override the `getTemplateFilepath` method in it
-   you can use a different `FeedItemInterface` implementation by extending its factory service
    (e.g., [GoogleFeedItemFactory]({{github.link}}/packages/product-feed-google/src/Model/FeedItem/GoogleFeedItemFactory.php))
-   you can even change the way the underlying Product entities are fetched from the database by extending the feed's product repository
    (e.g., [GoogleProductRepository]({{github.link}}/packages/product-feed-google/src/Model/Product/GoogleProductRepository.php))
-   when a more complicated customization is needed, extending feed item facade service and overwriting the `getItems` is the way to go
    (e.g., [GoogleFeedItemFacade]({{github.link}}/packages/product-feed-google/src/Model/FeedItem/GoogleFeedItemFacade.php)),
    it should allow you to provide your own way of getting the right items for your feed
