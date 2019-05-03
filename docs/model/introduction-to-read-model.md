# Introduction to Read Model

When using [entities](./entities.md) from the standard domain model, you might get a lot of data that you do not need for your particular use case.
This is not effective and often has a negative impact on the application performance.

The main goal of the read model in Shopsys Framework is a performance gain for the end user.
This is achieved by avoiding the usage of Doctrine entities (and hence calls to SQL Database) in particular frontend templates.

Each object in the read model has its specific purpose (e.g. there is `ListedProductView` object that is used on product lists only).
Unlike the entities, objects in the read model contain solely the information that are necessary for a particular use case
and their data can be aggregated from various sources (eg. Elasticsearch storage, and session).

The objects in read model are immutable, read-only by definition, and do not have any behavior.
