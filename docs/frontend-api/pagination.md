# Pagination
Shopsys Framework Frontend API uses pagination inspired by [Relay cursor connection](https://facebook.github.io/relay/graphql/connections.htm)

Every paginated entity uses connection with information about current page and paginated entities.
For more information please see [the specification](https://facebook.github.io/relay/graphql/connections.htm)

## Usage

If you want to get eg. paginated products you can use `products` query.
```text
{
  products{}
}
```

This query will return a `connection` object which consists of `pageInfo` and `edges`.

`pageInfo` is object that represents information about current page of pagination that you are on.

`edges` are array of objects that are generated and represent the products that you get.
 `edge` need to consist of `cursor` (pointer to products location) and `node` (data of given product that you requested).
 When you define connection you need to specify what should be the type of `node` (eg. `Product` or `String`)

 To get your products you need to simply write query that gets you the data that you need from the `node` field of `edge` with `first` or `last` parameters:
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

 To get next page you simply need to add the `after` parameter if you are using `first` or `before` of you are using `last` with cursor of a node
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
