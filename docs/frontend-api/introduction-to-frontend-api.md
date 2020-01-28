# Introduction to Frontend API

Shopsys Framework Frontend API is an interface to the application that is used for integration with external store frontend, for example, JS storefront od mobile app.
We use [GraphQL](https://graphql.org) (implemented using [overblog/GraphQLBundle](https://github.com/overblog/GraphQLBundle)).

GraphQL is a query language for APIs and provides an understandable description of the data in the API,
which gives clients the power to ask for exactly what they need and nothing more,
makes it easier to evolve APIs over time, and enables powerful developer tools.

You should see the [GraphQL documentation](https://graphql.org/learn/) for more information and learn how to query the data you need.

## Installation
Frontend API package is installed by default in `shopsys/project-base`.  
In case you want to add the package into an already existing project, you should follow [upgrade instructions](https://github.com/shopsys/shopsys/blob/master/upgrade/UPGRADE-v9.0.0-dev.md)

## Configuration
Frontend API is disabled by default and you need to enable it for each domain for which you want to use it.
In your `parameters_common.yml` file add new parameter `shopsys.frontend_api.domains` with the array of desired domain IDs.
```yaml
parmeters:
    # ...
    shopsys.frontend_api.domains:
        - 1
        - 2
```

You can also configure the place from which are the data for products taken from by choosing implementation of `ProductOnCurrentDomainFacadeInterface`.  
You can find more about this feature in [separate article](../model/front-end-product-filtering.md).

## Try it
GraphQL endpoint is available directly on the domain of your online store on the `/graphql/` path (ie. while running locally on Docker, it's http://127.0.0.1:8000/graphql/).

You can send a simple query with curl to see it in action:
```sh
curl -X POST http://127.0.0.1:8000/graphql/ -H "Content-Type: application/json" -d '{"query":"{ categories { name } }"}'
```
And you get back following JSON containing names of all root categories on the domain
```json
{
    "data": {
        "categories": [
            { "name": "Electronics" },
            { "name": "Books" },
            { "name": "Toys" },
            { "name": "Garden tools" },
            { "name": "Food" }
        ]
    }
}
```

### Working with Domains
Frontend API respects the domain you call so in case of the standard two domain setup with default data fixtures,
you can get data for the first domain with the request made to `http://127.0.0.1:8000/graphql/` while requesting `http://127.0.0.2:8000/graphql/` returns data for the second domain.

### Debug your queries
Frontend API package also integrates graphical interactive in-browser GraphQL IDE [GraphiQL](https://github.com/graphql/graphiql/tree/master/packages/graphiql#readme).
With it, you can debug your query easily and also browse the endpoint documentation for available objects, fields and their meaning.

You can access it in development mode on `http://127.0.0.1/graphql/graphiql` (respectively `http://127.0.0.2/graphql/graphiql` for the second domain).

You can also use other tools like Postman (<https://www.getpostman.com>) or GraphQL Playground (<https://github.com/prisma-labs/graphql-playground>).

## Extensibility of the API
Base fields, types, and objects are defined in the `shopsys/frontend-api` package as [decorators](https://github.com/overblog/GraphQLBundle/blob/0.12/docs/definitions/type-inheritance.md).

In your project are prepared specific implementations you can adjust as you want.
The configurations are just YAML files with the definition of [Object types and fields](https://graphql.org/learn/schema/#type-system).

### Object types
As an example, we take the Category object type. Each type has defined the decorator in `frontend-api` package and specific implementation in the project itself.
That allows us to introduce new types and evolve the API without the huge amount of work in the projects.

The category decorator in the Frontend API is defined as
```yaml
CategoryDecorator:          # Object is named "Category"
    type: object            # Object Type, meaning it's a type with some fields.
    decorator: true         # Defined as the decorator so it's used as a template and will not exists in the final schema
    config:
        description: "Represents a category"  # Description of the object type that appears in the endpoint documentation
        fields:
            uuid:                   # Field is named "uuid"
                type: "ID!"         # Built-in ID scalar type represents a unique identifier. The exclamation mark means that the field is non-nullable.
                description: "UUID" # Description of the field type that appears in the endpoint documentation
            name:
                type: "String"      # String is one of the built-in scalar types
                description: "Localized category name (domain dependent)"
            children:
                type: "[Category!]"
                description: "Descendant categories"
            parent:
                type: "Category"
                description: "Ancestor category"
```

The `Category` object type in your project in `config/graphql/types/Category.types.yml` is the one will be really used and you can adjust it as you want.
For example adding new field to the `Category` could be like:
```diff
 Category:
     type: object
     inherits:
         - 'CategoryDecorator'   # Inherits from the decorator defined earlier
+    config:
+        fields:                 # Fields from the decorator are added automatically
+            extId:              # New field you need in your project
+                type: "String"
+                description: "External category ID"
```

!!! note
    Fields in the definitions have to be named the same way as they are in the appropriate entity (in this case `\App\Model\Category\Category`)

### Query type
The base query type is defined with the decorator approach the same way as objects are.
```yaml
QueryDecorator:
    type: object
    decorator: true
    config:
        fields:
            categories:
                type: '[Category!]!'                    # Array of the categories will be returned.
                resolve: "@=resolver('categories')"   # Define the resolver responsible for returning the data. See the resolvers section below.
```

And specific `Query` type is defined in `config/graphql/types/Query.types.yml`
```yaml
Query:
    type: object
    inherits:
        - 'QueryDecorator'   # No project-specific queries are defined.
```

### Resolvers
Resolvers are normal Symfony services.
They only have to implement the `Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface` to be recognized as an available resolver for the GraphQL.

It is several ways how to define resolvers in definition YAML files.
We use `Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface` to keep definitions simple and easy to read.

`AliasedInterface` describe one method `getAliases` that should return array of `"method name" => "name used in definition"` pairs.

```php
/**
 * @return array
 */
public function resolve(): array
{
    // implementation
}

/**
 * @return string[]
 */
public static function getAliases(): array
{
    return [
        'resolve' => 'categories',  // field with resolver defined as "@=resolver('categories')" (see above) will use `resolve` method in this class
    ];
}
```

You can override resolvers like any other Symfony service.

### Resolver Maps
If we map GraphQl objects to entities, it may happen that automatic transformation is not possible.

This can happen when we want to use getter for some entity attribute and such getter requires parameter.

For this transformation we can use a `ResolverMap` object .
`ResolverMap` is a Symfony service that implements `Overblog\GraphQLBundle\Resolver\ResolverMapInterface`.
ResolverMap can be created as a child of `Overblog\GraphQLBundle\Resolver\ResolverMap` class too and overload the `map` method.

Example of `ResolverMap`:

```php
class ProductResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Product' => [
                'shortDescription' => function (Product $product) {
                    return $product->getShortDescription($this->domain->getId());
                },
                'link' => function (Product $product) {
                    return $this->getProductLink($product);
                },
            ],
        ];
    }
}
```

You  can register `ResolverMap` in `config/packages/shopsys_frontend_api.yml`:

```yaml
overblog_graphql:
    definitions:
        schema:
            ...
            resolver_maps:
                - Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductResolverMap
```

you can read more info about `ResolveMap` in [documentation](https://github.com/overblog/GraphQLBundle/blob/master/docs/definitions/resolver-map.md).
