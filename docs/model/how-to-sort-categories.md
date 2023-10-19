# How to Sort Categories

Sorting of categories may become challenging with an increasing number of categories. This article will help you understand the basic approach to category sorting from different perspectives (sorting in admin, adding a new category, complete import).

## Sorting in administration

In administration is leveraged the use of [nestedSortable jQuery plugin](https://github.com/ilikenwf/nestedSortable) which provides a complete calculated [nested set model](https://en.wikipedia.org/wiki/Nested_set_model) 
and this model is updated entirely after category sorting is saved.
This approach proved to be the best for many categories while does not impact the performance of the small data set.
You can take a look at the `Shopsys\FrameworkBundle\Model\Category\CategoryFacade::reorderByNestedSetValues()` method for details.

## Reorder the whole tree in your code

Whenever you need to reorder the whole tree, and you have the complete data,
you can call `CategoryFacade::reorderByNestedSetValues()` method which accepts a complete calculated nested set model in the following format

```php
[
    [
        'id' => 2,
        'parent_id' => null,
        'depth' => 0,
        'left' => 1,
        'right' => 14,
    ],
    [
        'id' => 3,
        'parent_id' => 2,
        'depth' => 1,
        'left' => 2,
        'right' => 3,
    ],
    // ...
]
```

!!! note

    Categories with `parent_id` set to null will be at the main level, placed under hidden root category (see `CategoryFacade::getRootCategory()`).<br>
    This root category must not be present in the data passed to reordering method.

Usually, you don't have a complete nested set model, but it's much easier to obtain a sorted [adjacency list](https://en.wikipedia.org/wiki/Adjacency_list) (for example, from the information system).
In that case, it's possible to use `Shopsys\FrameworkBundle\Model\Category\CategoryNestedSetCalculator::calculateNestedSetFromAdjacencyList()` helper method to calculate the complete nested set model.

Usage is pretty straightforward. The desired category tree structure is following (each node already contain computed left and right attribute for better understanding)

![category tree structure](./img/category-tree.png 'category tree structure')

We can easily obtain sorted adjacency list from a data source

```php
$parentIdByCategoryId = [
    2 => null,
    3 => 2,
    4 => 2,
    5 => 4,
    6 => 4,
    7 => 2,
    8 => 7,
    9 => null,
    11 => 9,
    10 => null,
    12 => null,
];
```

At this point we pass this data to previously mentioned helper method and sort the whole tree

```php
use Shopsys\FrameworkBundle\Model\Category\CategoryNestedSetCalculator;

$categoriesOrderingData = CategoryNestedSetCalculator::calculateNestedSetFromAdjacencyList($parentIdByCategoryId);

$this->categoryFacade->reorderByNestedSetValues($categoriesOrderingData);
```

## Add a new category programmatically

When you're adding a complete new category, you usually don't need to sort the whole tree again.  
You can leverage the fact the `CategoryRepository` extends the `NestedTreeRepository`,
so you have really helpful methods at your disposal to place a category to first or last place, or after a specific other category.

For the full list, check out the [official documentation](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/tree.md#basic-usage-examples)

## Import categories

If you're interested to know the best practices about importing categories, you can take a look at the [import categories cookbook](../cookbook/import-categories.md).

## When things go wrong

It is possible that the category nested set became corrupted.
Either intentionally (import directly to the database) or by mistake.

In that case, you can run following command to recalculate the whole nested set

```sh
php bin/console shopsys:categories:recalculate
```

!!! important

    As it's not possible to obtain the correct order from the adjacency list, it's possible that siblings (categories on the same level) may not be sorted the way you want.<br>
    You can re-sort them later in administration.

The tree is recalculated only when corrupted, so you don't need to worry about your data.
