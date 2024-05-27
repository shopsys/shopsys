# Pagination

Shopsys Platform Frontend API uses pagination inspired by [Relay cursor connection](https://facebook.github.io/relay/graphql/connections.htm)

Every paginated entity uses a connection with information about the current page and paginated entities.
For more information, please see [the specification](https://facebook.github.io/relay/graphql/connections.htm)

## Usage

If you want to get e.g., paginated products, you can use the `products` query.

```text
{
  products{}
}
```

This query will return a `connection` object which consists of `pageInfo`, `edges` and `totalCount`.

`pageInfo` is an object that represents information about the current page of pagination that you are on.  
`edges` are an array of objects that are generated and represent the products you get.  
`totalCount` is a total number of products available for query.
`edge` needs to consist of `cursor` (pointer to products location) and `node` (data of given product that you requested).  
When you define connection, you need to specify what should be the type of `node` (e.g., `Product` or `String`)

To get your products, you need to simply write a query that gets you the data that you need from the `node` field of `edge` with `first` or `last` parameters:

```text
{
  products (first:10) {
    edges {
      cursor
      node {
        name
        link
        shortDescription
      }
    }
  }
}

// OR

{
  products (last:10) {
    edges {
      cursor
      node {
        name
        link
        shortDescription
      }
    }
  }
}
```

To get to the next page, you simply need to add the `after` parameter if you are using `first` or `before` of you are using `last` with the cursor of a node.

```text
{
  products (first:10, after: "YXJyYXljb25uZWN0aW9uOjk=") {
    edges {
      cursor
      node {
        name
        link
        shortDescription
      }
    }
  }
}

// OR

{
  products (last:10, before: "YXJyYXljb25uZWN0aW9uOjEw") {
    edges {
      cursor
      node {
        name
        link
        shortDescription
      }
    }
  }
}
```

To order your result, add the `orderingMode` parameter, which is GraphQL enum type `ProductOrderingModeEnum`.
You cannot use other values than supported values of the specified type.
If you want to add more ordering modes, feel free to extend the type in `ProductOrderingModeEnum.types.yaml`.
Default ordering is `relevance` for search and `priority` for other queries.

```text
{
  products (orderingMode: NAME_ASC) {
    edges {
      cursor
      node {
        name
        link
        shortDescription
      }
    }
  }
}
```
