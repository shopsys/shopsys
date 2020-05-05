# Elasticsearch
To provide the best possible performance, frontend searching, filtering and autocomplete
leverages [Elasticsearch technology](https://www.elastic.co/products/elasticsearch).
Elasticsearch is a super fast no-SQL database where data are stored in JSON format as so-called [documents](https://www.elastic.co/guide/en/elasticsearch/reference/current/_basic_concepts.html#_document) in one or more [indexes](https://www.elastic.co/guide/en/elasticsearch/reference/current/_basic_concepts.html#_index).

## How does it work
All data are stored in PostgreSQL by default but querying relational database might not be fast enough.
Therefore, relevant attributes are also stored in Elasticsearch index under the same ID.
When data need to be searched or filtered on the frontend, the query is sent to Elasticsearch.
As a result, found row IDs are returned from Elasticsearch and then the row data are loaded from PostgreSQL database into entities using Doctrine ORM.

### Elasticsearch index setting
Elasticsearch [index](https://www.elastic.co/blog/what-is-an-elasticsearch-index) is a logical namespace, you can imagine single index as a single database in terms of relational databases.

The Elasticsearch indexes are created during application build.
You can also create or delete indexes manually using Phing targets `elasticsearch-index-create`, and `elasticsearch-index-delete` respectively, or you can use `elasticsearch-index-recreate` that encapsulates the previous two.

!!! hint
    More information about what Phing targets are and how they work can be found in [Console Commands for Application Management (Phing Targets)](../introduction/console-commands-for-application-management-phing-targets.md)

Unique index is created for each domain as some attributes can have distinct values for each domain.
To discover the exact mapping setting, you can look at the JSON configuration files
that are located in `src/Resources/definition/` directory in [`shopsys/project-base`](https://github.com/shopsys/project-base).
The directory is configured using `%shopsys.elasticsearch.structure_dir%` parameter.

#### Available elasticsearch phing targets

- elasticsearch-index-create
- elasticsearch-index-delete
- elasticsearch-index-recreate
- elasticsearch-index-migrate
- elasticsearch-export
- elasticsearch-export-changed

These commands takes action for all registered indexes. You can also define a single index for given action by defining parameter `elasticsearch.index` (e.g. `elasticsearch-index-recreate -D elasticsearch.index=product` will recreate a structure for index `product`).

### Product data export
No data are automatically stored in Elasticsearch by "itself".
When you store data into a relational database, they are not stored in Elasticsearch.
You have to export data from the database into Elasticsearch actively.

Following product attributes are exported into Elasticsearch (i.e. the search or filtering can be performed on these fields only):

* name
* catnum
* partno
* ean
* description
* short description
* flags (IDs of assigned flags)
* brand (ID of assigned brand)
* categories (IDs of assigned categories)
* prices (all the prices for all pricing groups)
* in_stock (true/false value whether the product is in stock)
* parameters (pairs of parameter IDs and parameter value IDs)
* ordering_priority (priority number)
* calculated_selling_denied (calculated true/false value whether the product is already sold out)
* selling_denied (true/false value whether the product can be sold)
* availability (translation of product availability)
* main_variant (true/false value whether the product is main variant or not. You can find more about behaviour of variants [here](../functional/behavior-of-product-variants.md))
* detail_url (absolute url to page with products detail)
* visibility (all visibilities for all pricing groups and domains)

Data of all products are exported into Elasticsearch by CRON module (`ProductExportCronModule.php`) every 5 minutes.
Alternatively, you can force the export manually using `elasticsearch-export -D elasticsearch.index=product` Phing target.

If you need to change the data that are exported into Elasticsearch, overwrite appropriate methods in `ProductExportRepository` and `ProductElasticsearchConverter` classes.

## Use of Elasticsearch
Elasticsearch is used to search, filter and sort products on the frontend and to display products in listing via [Read Model](./introduction-to-read-model.md).
You can learn more about [Product searching](../model/front-end-product-searching.md) and [Product filtering](../model/front-end-product-filtering.md) in particular articles.
[Sorting](../introduction/how-to-set-up-domains-and-locales.md#37-sorting-in-different-locales) is done with the help of [ICU analysis plugin](https://www.elastic.co/guide/en/elasticsearch/plugins/current/analysis-icu.html)
which ensures that alphabetical sorting is correct for every language and its set of rules.

### Adding new index
To add another index to export, you need to configure mapping in JSON file, create an index class which extends `AbstractIndex` and implement all abstract methods and register it into `services.yaml`.
The index will be immediately available for phing commands. For detailed explanation see a [cookbook](../cookbook/adding-a-new-elasticsearch-index.md)

#### AbstractIndex methods
AbstractIndex contains some abstract methods which needs to be implemented for proper behavior.

##### getName()
Must return index name (same as a parent directory of JSON mapping).

##### getTotalCount()
Must return number of total rows you want to have exported for given index.

##### getExportDataForIds()
Must return all rows for given row ID. It is used for partial exports.

##### getExportDataForBatch()
Must return all rows which you want to have exported into elasticsearch.

##### getChangedCount()
Optional - must return the number of changed rows you want to have exported for given index. Used for export of only changed rows.

##### getChangedIdsForBatch()
Optional - must return IDs of rows you want to have exported into elasticsearch. Used for export of only changed rows.

## Where does Elasticsearch run?
When using docker installation, Elasticsearch API is available on the address [http://127.0.0.1:9200](http://127.0.0.1:9200).

## How to change the default index and data export setting?
If you wish to reconfigure the indexes setting, simply change the JSON configurations in `src/Resources/definition/`.
Configurations use the `<index>/<domain_id>.json` naming pattern.

## Known issues
* When you need to add a new domain, you have to do following steps:
    * create elasticsearch definition for the domain
    * delete indexes
    * create indexes
    * export products

## Troubleshooting

#### Check exported product

You can easily check if there is a product exported in the Elasticsearch by putting following url address into your browser
  `http://127.0.0.1:9200/{domain ID}/{product ID}?pretty`
  eg. `http://127.0.0.1:9200/1/52?pretty`

#### Export fails

If the export fails with a following error (or similar)

```no-highlight
  [Elasticsearch\Common\Exceptions\Forbidden403Exception (403)]
  ...{"type": "cluster_block_exception", "reason": "blocked by: [FORBIDDEN/12/index read-only / allow delete (api)];"}...
```

It means the Elasticsearch switched into a read-only mode. Possible reason is that you have almost full disk, default value when Elasticsearch switch into read-only mode is `95%`.

Solution is to make more space on your hard drive, and then manually release the read-only mode by running following console command:
```sh
curl -XPUT -H "Content-Type: application/json" http://localhost:9200/_all/_settings -d '{"index.blocks.read_only_allow_delete": null}'
```

You can find more information in [https://www.elastic.co/guide/en/elasticsearch/reference/6.2/disk-allocator.html](https://www.elastic.co/guide/en/elasticsearch/reference/6.2/disk-allocator.html).
