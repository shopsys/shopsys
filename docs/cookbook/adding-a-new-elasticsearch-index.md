# Adding a New Elasticsearch Index

In this cookbook, we will add a new Elasticsearch index for categories, implement basic functions for data export, implement a cron module, and support for partial export.

## New Elasticsearch mapping

As a first step, we need to define [Elasticsearch mapping](https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping.html) in `src/Resources/definition/category/` for all domains (e.g., for 2 domains with ID 1 and 2: `1.json`, `2.json`).

Example of mapping part for English domain with ID 1 (`category/1.json`)

```json
{
  "settings": {
    "index": {
      "number_of_shards": 1,
      "number_of_replicas": 0
    },
    "analysis": {
      "filter": {
        "english_stop": {
          "type": "stop",
          "stopwords": "_english_"
        },
        "english_stemmer": {
          "type": "stemmer",
          "language": "english"
        },
        "edge_ngram": {
          "type": "edgeNGram",
          "min_gram": 2,
          "max_gram": 20
        }
      },
      "tokenizer": {
        "keep_special_chars": {
          "type": "pattern",
          "pattern": "[^\\p{L}\\d-/]+"
        }
      },
      "analyzer": {
        "full_with_diacritic": {
          "tokenizer": "keep_special_chars",
          "filter": [
            "lowercase"
          ]
        },
        "full_without_diacritic": {
          "tokenizer": "keep_special_chars",
          "filter": [
            "lowercase",
            "asciifolding"
          ]
        },
        "stemming": {
          "tokenizer": "standard",
          "filter": [
            "lowercase",
            "english_stemmer",
            "english_stop",
            "asciifolding"
          ]
        },
        "edge_ngram_with_diacritic": {
          "tokenizer": "keep_special_chars",
          "filter": [
            "edge_ngram",
            "lowercase"
          ]
        },
        "edge_ngram_without_diacritic": {
          "tokenizer": "keep_special_chars",
          "filter": [
            "edge_ngram",
            "lowercase",
            "asciifolding"
          ]
        },
        "edge_ngram_without_diacritic_html": {
          "char_filter": "html_strip",
          "tokenizer": "keep_special_chars",
          "filter": [
            "edge_ngram",
            "lowercase",
            "asciifolding"
          ]
        },
        "edge_ngram_unanalyzed": {
          "tokenizer": "keyword",
          "filter": [
            "edge_ngram"
          ]
        }
      }
    }
  },
  "mappings": {
    "properties": {
    "name": {
      "type": "text",
      "analyzer": "stemming",
      "fields": {
        "full_with_diacritic": {
          "type": "text",
          "analyzer": "full_with_diacritic"
        },
        "full_without_diacritic": {
          "type": "text",
          "analyzer": "full_without_diacritic"
        },
        "edge_ngram_with_diacritic": {
          "type": "text",
          "analyzer": "edge_ngram_with_diacritic"
        },
        "edge_ngram_without_diacritic": {
          "type": "text",
          "analyzer": "edge_ngram_without_diacritic"
        },
        "keyword": {
          "type": "icu_collation_keyword",
          "language": "en",
          "index": false
        }
      }
    },
    "description": {
      "type": "text",
      "analyzer": "edge_ngram_without_diacritic_html"
    },
    "parrent_id": {
      "type": "integer"
    },
    "level": {
      "type": "integer"
    },
    "uuid": {
      "type": "text"
    }
    }
  }
}
```

## New CategoryIndex

Registering a child of `Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex` into the application will allow you to manage the Elasticsearch index easily for common tasks (create, delete, migrate structure and export data).
All you need to do is implement abstract methods from the parent class. In this cookbook, we will do it step by step.

Create class `CategoryIndex` in `src/Model/Category/Elasticsearch`.
The class must extend the class `Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex`.

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;

class CategoryIndex extends AbstractIndex
{
    /**
     * @return string
     */
    public static function getName(): string
    {
        // TODO: Implement getName() method.
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getTotalCount(int $domainId): int
    {
        // TODO: Implement getTotalCount() method.
    }

    /**
     * @param int $domainId
     * @param array $restrictToIds
     * @return array
     */
    public function getExportDataForIds(int $domainId, array $restrictToIds): array
    {
        // TODO: Implement getExportDataForIds() method.
    }

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return array
     */
    public function getExportDataForBatch(int $domainId,int $lastProcessedId,int $batchSize) : array
    {
        // TODO: Implement getExportDataForBatch() method.
    }
}
```

Register new index into `config/services.yaml`

```yaml
    App\Model\Category\Elasticsearch\CategoryIndex: ~
```

## Create a new index in Elasticsearch

To create an index in the Elasticsearch, we need to implement `getName()` method in `CategoryIndex`.

The name must be the same as the name of the directory for Elasticsearch mapping in `src/Resources/definition/`

```php
/**
 * @return string
 */
public static function getName(): string
{
    return 'category';
}
```

So far, it is the most minimalistic implementation.
Now, we are able to create an index in Elasticsearch by running `./phing elasticsearch-index-migrate -D elasticsearch.index=category`.

!!! warning
    If you are using version v9.1.0 or lower you have to use command `./phing elasticsearch-index-create -D elasticsearch.index=category` for creating index.


Also, we can use `./phing elasticsearch-index-recreate` or `./phing elasticsearch-index-delete`.

!!! note
    Command `./phing elasticsearch-index-migrate -D elasticsearch.index=category` (notice the parameter -D) create Elasticsearch index only for our CategoryIndex.
    Using `./phing elasticsearch-index-migrate` (without `-D` flag) will create Elasticsearch indexes for all registered ones in your project (product, category, and so on).

## Export data into Elasticsearch

Creating and deleting the index is nice, but it is not really useful.
As the next step, we will implement methods `getTotalCount()` and `getExportDataForBatch()` to be able to export data.

We can use the already existing method in `\Shopsys\FrameworkBundle\Model\Category\CategoryRepository::getTranslatedVisibleSubcategoriesByDomain()`.
The method `getTranslatedVisibleSubcategoriesByDomain()` needs as a second argument an instance of `DomainConfig`, so we need to inject an instance of `Domain` class along with the instance of `CategoryRepository` into `CategoryIndex`.

```php
/**
 * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
 */
protected $categoryRepository;

/**
 * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
 */
protected $domain;

/**
 * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
 * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 */
public function __construct(CategoryRepository $categoryRepository, Domain $domain)
{
   $this->categoryRepository = $categoryRepository;
   $this->domain = $domain;
}
```

When we have injected services we may implement `getTotalCount()`

```php
/**
 * @param int $domainId
 * @return int
 */
public function getTotalCount(int $domainId): int
{
    return count($this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain(
        $this->categoryRepository->getRootCategory(),
        $this->domain->getDomainConfigById($domainId)
    ));
}
```

and also a `getExportDataForBatch()` with a private converting method `convertToElastic()`

```php
/**
 * @param int $domainId
 * @param int $lastProcessedId
 * @param int $batchSize
 * @return array
 */
public function getExportDataForBatch(int $domainId,int $lastProcessedId,int $batchSize) : array
{
    $domainConfig = $this->domain->getDomainConfigById($domainId);
    $categories = $this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain(
        $this->categoryRepository->getRootCategory(),
        $domainConfig
    );
    $locale = $domainConfig->getLocale();
    foreach ($categories as $category) {
        $result[$category->getId()] = $this->convertToElastic($category, $domainId, $locale);
    }
    return $result;
}

/**
 * @param \App\Model\Category\Category $category
 * @param int $domainId
 * @param string $locale
 * @return array
 */
private function convertToElastic(Category $category, int $domainId, string $locale): array
{
    return [
        'name' => $category->getName($locale),
        'description' => $category->getDescription($domainId),
        'parentId' => $category->getParent()->getId(),
        'level' => $category->getLevel(),
        'uuid' => $category->getUuid(),
    ];
}
```

!!! note
    The `getExportDataForBatch()` must return a serialized array of rows indexed by its ID.

Now we can export categories data (name, description, parentId, level, and uuid) into Elasticsearch with `./phing elasticsearch-export -D elasticsearch.index=category` (index has to be created first, see the step above).

### Exporting via cron

We may automate the export process thanks to the `CronModule`, which is super easy.
To achieve this goal, we must create a new class `CategoryExportCronModule` in `src/Model/Category/Elasticsearch` which extends `AbstractExportCronModule`.

The most important task here is to override the parent constructor and change the type-hint of the first argument to our created index (`CategoryIndex`).

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportCronModule;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;

class CategoryExportCronModule extends AbstractExportCronModule
{
    /**
     * @param \App\Model\Category\Elasticsearch\CategoryIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain);
    }
}
```

Now, if we have [crons](../introduction/cron.md) properly configured, the new cron will be automatically started with others.
Or you may want to [configure](../cookbook/working-with-multiple-cron-instances.md) this cron module with different timing.

### Implement partial update

Sometimes, we want to export data immediately after the original data are changed (hiding category, rename, etc.)
For this purpose we need to implement `CategoryIndex::getExportDataForIds()`, scheduler for queuing, and [subscriber](https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber) for processing queue.

Anywhere you want to add a row to immediate export, you may call `CategoryExportScheduler::scheduleRowIdForImmediateExport()` to add a row to the queue.

#### Scheduler

The scheduler is used as a queue of IDs we want to export. When we make any changes, we may add an affected category ID into this queue, and the subscriber will pick it up after the request is done.

Create class `CategoryExportScheduler` which extends `AbstractExportScheduler`. We do not need to override nor implement any method.

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportScheduler;

class CategoryExportScheduler extends AbstractExportScheduler
{
}
```

#### Subscriber

Create class `CategoryExportSubscriber` which extends `AbstractExportSubsriber`.
Override its `__construct()` and `getSubscribedEvents()` methods.

Here is important to override constructors arguments type hint.
Instead of abstract classes from `\Shopsys\FrameworkBundle\Component\Elasticsearch`, you need to replace it with our new implementation (`CategoryExportScheduler`, `CategoryIndex`).

We have to implement `getSubscribedEvents()`, which is desired to listening for kernel response, and then call the parent's method `exportScheduledRows()` with the proper priority.
More information about subscribers can be found in [Symfony documentation](https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber).

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportSubscriber;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Symfony\Component\HttpKernel\KernelEvents;

class CategoryExportSubscriber extends AbstractExportSubscriber
{
    /**
     * @param \App\Model\Category\Elasticsearch\CategoryExportScheduler $categoryExportScheduler
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \App\Model\Category\Elasticsearch\CategoryIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryExportScheduler $categoryExportScheduler,
        EntityManagerInterface $entityManager,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        CategoryIndex $index,
        Domain $domain
    ) {
        parent::__construct($categoryExportScheduler, $entityManager, $indexFacade, $indexDefinitionLoader, $index, $domain);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['exportScheduledRows', 0],
            ],
        ];
    }
}
```

#### CategoryIndex::getExportDataForIds()

To finish partial exports, we need to implement the last unimplemented method in `CategoryIndex` – to return all categories by their identifiers.
We may also use an already existing method from `CategoryRepository`.

```php
/**
 * @param int $domainId
 * @param array $restrictToIds
 * @return array
 */
public function getExportDataForIds(int $domainId, array $restrictToIds): array
{
    $categories = $this->categoryRepository->getCategoriesByIds($restrictToIds);

    $domainConfig = $this->domain->getDomainConfigById($domainId);
    $locale = $domainConfig->getLocale();
    foreach ($categories as $category) {
        $result[$category->getId()] = $this->convertToElastic($category, $domainId, $locale);
    }
    return $result;
}
```

### Export only changed categories

Now, when we have a way to export partial data, we can extend the functionality even more and allow to export only changed categories.
For this purpose, we need our index to implement `\Shopsys\FrameworkBundle\Component\Elasticsearch\IndexSupportChangesOnlyInterface` interface and add two more methods: `CategoryIndex::getChangedCount()` and  `CategoryIndex::getChangedIdsForBatch()`.

!!! note
    You will need to implement some way to distinguish changed categories.
    For this purpose, you can, for example, [add a new attribute to an entity](./adding-new-attribute-to-an-entity.md) or implement some sort of queue.

#### CategoryIndex::getChangedIdsForBatch()

This method has to return ID of categories that are considered changed and should be exported.
If you use an entity attribute to distinguish changed categories, you can just simply return categories with this attribute set.

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractIndex;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexSupportChangesOnlyInterface;

class CategoryIndex extends AbstractIndex implements IndexSupportChangesOnlyInterface
{

    // ...

    /**
     * @param int $domainId
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return int[]
    */
    public function getChangedIdsForBatch(int $domainId, int $lastProcessedId, int $batchSize): array
    {
        $allCategories = $this->categoryRepository->getTranslatedVisibleSubcategoriesByDomain(
            $this->categoryRepository->getRootCategory(),
            $this->domain->getDomainConfigById($domainId)
        );

        $categoryIds = [];
        foreach ($allCategories as $category) {
            // boolean entity attribute `shouldBeExported` have to be added before. See "Adding New Attribute to an Entity" cookbook
            if ($category->shouldBeExported === true) {
                $categoryIds[] = $category->getId();
            }
        }

        return $categoryIds;
    }
}
```

!!! note
    In a real application, you should implement the logic with the database query to avoid fetching all data unnecessarily.

!!! note
    In this cookbook, we return all affected categories at once.
    On larger data sets, you can offset and limit the results with the `$lastProcessedId` and `$batchSize`.

#### CategoryIndex::getChangedCount()

This method has to return the count of changed categories.

```php
/**
 * @param int $domainId
 * @return int
*/
public function getChangedCount(int $domainId): int
{
    return count($this->getChangedIdsForBatch($domainId));
}
```

### Exporting changed categories via cron

We may automate the export process like we did for full data export earlier.
All we need to do is to create a new class `CategoryExportChangedCronModule` in `src/Model/Category/Elasticsearch` which extends `AbstractExportChangedCronModule`.

The most important task here is to override the parent constructor and change the type-hint of the first argument to our created index (`CategoryIndex`).

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractExportCronModule;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;

class CategoryExportChangedCronModule extends AbstractExportChangedCronModule
{
    /**
     * @param \App\Model\Category\Elasticsearch\CategoryIndex $index
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        CategoryIndex $index,
        IndexFacade $indexFacade,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain
    ) {
        parent::__construct($index, $indexFacade, $indexDefinitionLoader, $domain);
    }
}
```

### Mark exported categories as exported

Export now looks for the categories with the attribute `shouldExport` set to `true`.
This is convenient, but after the export is finished, we should set the attribute back, so the next iterations will not export the same category over and over.

For this, we can use `IndexExportedEvent::INDEX_EXPORTED` event, which is fired after each index is finished for all domains.

We create a simple subscriber to this event and mark categories as exported.

```php
declare(strict_types=1);

namespace App\Model\Category\Elasticsearch;

use App\Model\Category\Category;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent;

class CategoryExportedSubscriber {

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexExportedEvent $indexExportedEvent
     */
    public function markAllAsExported(IndexExportedEvent $indexExportedEvent): void
    {
        if ($indexExportedEvent->getIndex() instanceof CategoryIndex) {
            $this->entityManager
                ->createQuery('UPDATE ' . Category::class . ' c SET c.shouldExport = FALSE')
                ->execute();

        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IndexExportedEvent::INDEX_EXPORTED => 'markAllAsExported',
        ];
    }
}
```

## Conclusion

We have created a new index category in Elasticsearch.
We were able to fill it with data (by a cron or immediately after a row is changed).

Categories can be marked for export (from different parts of the application, integration with IS, etc.), and only such categories can be exported.

From now you are able to use Elasticsearch as a data source (data providing functionality is needed to be implemented) instead of PostgreSQL, which will improve the performance of your application.
