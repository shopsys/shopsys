# Introduction to Frontend API

Shopsys Platform Frontend API is an interface to the application that is used for integration with external store frontend, for example, JS Storefront or a mobile app.
We use [GraphQL](https://graphql.org) (implemented using [overblog/GraphQLBundle](https://github.com/overblog/GraphQLBundle)).

GraphQL is a query language for APIs and provides an understandable description of the data in the API,
which gives clients the power to ask for exactly what they need and nothing more,
makes it easier to evolve APIs over time, and enables powerful developer tools.

You should see the [GraphQL documentation](https://graphql.org/learn/) for more information and learn how to query the data you need.

## Installation

The Frontend API package is installed by default in `shopsys/project-base`.

## Configuration

You have to create a pair of private and public keys for signing access tokens with the command `./phing frontend-api-generate-new-keys`.
Note that when you regenerate the keys in the future, you invalidate all issued access and refresh tokens.
You can read more about tokens in part [authentication](./authentication.md).

## Try it

GraphQL endpoint is available directly on your online store's domain on the `/graphql/` path (ie. while running locally on Docker, it's http://127.0.0.1:8000/graphql/).

You can send a simple query with curl to see it in action:

```sh
curl -X POST http://127.0.0.1:8000/graphql/ -H "Content-Type: application/json" -d '{"query":"{ categories { name } }"}'
```

You get back the following JSON containing names of all root categories on the domain

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

Frontend API respects the domain you call, so in the case of the standard two domain setup with default data fixtures,
you can get data for the first domain with the request made to `http://127.0.0.1:8000/graphql/` while requesting `http://127.0.0.2:8000/graphql/` returns data for the second domain.

### Working with Currencies

When a domain has more than one currency configured (the `currencies` key in `config/domains.yaml`), the API resolves the currency of the returned prices from the `X-Currency-Code` HTTP header sent with the request:

```sh
curl -X POST http://127.0.0.1:8000/graphql/ \
    -H 'Content-Type: application/json' \
    -H 'X-Currency-Code: CZK' \
    -d '{"query": "{ settings { pricing { currentCurrencyCode } } }"}'
```

The header is read on every request, so no query or mutation needs a currency argument.
When the header is missing, contains an unknown code, or a currency not enabled on the domain, the API silently falls back to the domain default currency.
Every `Price` in the API contains a `currencyCode` field with the currency the price is expressed in.
The currencies enabled on the domain (together with the currently selected one) can be queried from `settings { pricing { currentCurrencyCode, availableCurrencies { code } } }`.

The order prices are always returned in the currency the order was placed in (`order { currencyCode }`), regardless of the currently selected currency.

The default storefront persists the customer's selected currency in the `currencyCode-{domainId}` cookie and sends the header automatically with every API request.

### Requesting API from another domain (handling CORS)

[overblog/GraphQLBundle](https://github.com/overblog/GraphQLBundle) comes out of the box with a generic and simple [CORS (Cross-Origin Resource Sharing)](http://enable-cors.org/) handler.
The handler is disabled by default, so your API is better protected from attacks by malicious scripts.

You can enable the simple CORS handler in `config/packages/shopsys_frontend_api.yaml` file:

```diff
    overblog_graphql:
+       security:
+           handle_cors: true
        definitions:
            schema:
            # ...
```

You can read more in [OverblogGraphQLBundle documentation](https://github.com/overblog/GraphQLBundle/blob/v0.13.4/docs/security/handle-cors.md)

!!! warning

    The default CORS handler provides only basic configuration. For example, resources cannot be shared only with the specific domain.<br>
    You can gain more flexibility with [NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle).

### Debug your queries

Frontend API package also integrates graphical interactive in-browser GraphQL IDE [GraphiQL](https://github.com/graphql/graphiql/tree/master/packages/graphiql#readme).
With it, you can easily debug your query and browse the endpoint documentation for available objects, fields, and their meaning.

You can access it in development mode on `http://127.0.0.1:8000/graphql/graphiql` (respectively `http://127.0.0.2:8000/graphql/graphiql` for the second domain).

You can also use other tools like Postman (<https://www.getpostman.com>) or GraphQL Playground (<https://github.com/prisma-labs/graphql-playground>).

## Extensibility of the API

Base fields, types, and objects are defined in the `shopsys/frontend-api` package as [decorators](https://github.com/overblog/GraphQLBundle/blob/0.12/docs/definitions/type-inheritance.md).

In your project are prepared specific implementations you can adjust as you want.
The configurations are just YAML files with the definition of [Object types and fields](https://graphql.org/learn/schema/#type-system).

### Object types

As an example, we take the Category object type. Each type has defined the decorator in the `frontend-api` package and specific implementation in the project itself.
That allows us to introduce new types and evolve the API without the huge amount of work in the projects.

The category decorator in the Frontend API is defined as

```yaml
CategoryDecorator: # Object is named "Category"
    type: object # Object Type, meaning it's a type with some fields.
    decorator: true # Defined as the decorator so it's used as a template and will not exist in the final schema
    config:
        description: 'Represents a category' # Description of the object type that appears in the endpoint documentation
        fields:
            uuid: # Field is named "uuid"
                type: 'ID!' # Built-in ID scalar type represents a unique identifier. The exclamation mark means that the field is non-nullable.
                description: 'UUID' # Description of the field type that appears in the endpoint documentation
            name:
                type: 'String' # String is one of the built-in scalar types
                description: 'Localized category name (domain dependent)'
            children:
                type: '[Category!]'
                description: 'Descendant categories'
            parent:
                type: 'Category'
                description: 'Ancestor category'
```

The `Category` object type in your project in `config/graphql/types/Category.types.yaml` is the one will be really used and you can adjust it as you want.
For example, adding a new field to the `Category` could be like:

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
                type: '[Category!]!' # Array of the categories will be returned.
                resolve: "@=query('categoriesQuery')" # Define the query responsible for returning the data. See the 'Queries' section below, e.g., CategoriesQuery::categoriesQuery.
```

And specific `Query` type is defined in `config/graphql/types/Query.types.yaml`

```yaml
Query:
    type: object
    inherits:
        - 'QueryDecorator' # No project-specific queries are defined.
```

### Queries

Queries are normal Symfony services.
They only have to extend `Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery` to be recognized as an available query for the GraphQL.
This abstract class provides convenience for registering query functions by introducing a function naming convention for use in Query.types.yaml definitions.
The suffix `Query` in the name of the function is required for calling the function as the alias in the query decorator (`resolve: "@=query('categoriesQuery')"`).
For example, if you want to get all categories in the GQL query categories, the function's name must be `categoriesQuery`.

```php
class CategoriesQuery extends AbstractQuery
{
    /**
     * @return array
     */
    public function categoriesQuery(): array
    {
        // implementation

        return [];
    }
}
```

### Mutations

Mutations are normal Symfony services.
They only have to extend `Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation` to be recognized as an available mutation for the GraphQL.
This abstract class provides convenience for registering mutation functions by introducing a function naming convention for use in Mutation.types.yaml definitions.
The suffix `Mutation` in the name of the function is required for calling the function as the alias in query decorator (`resolve: "@=mutation('createOrderMutation', args, validator)"`).
For example, if you want to create an order by the GQL mutation, the function's name will be `createOrderMutation`.

```php
class CreateOrderMutation extends AbstractMutation
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Overblog\GraphQLBundle\Validator\InputValidator $validator
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrderMutation(Argument $argument, InputValidator $validator): Order
    {
        // implementation

        return $order;
    }
}
```

You can override both queries and mutations like any other Symfony service.

### Resolver Maps

If we map GraphQl objects to entities, it may happen that automatic transformation is not possible.

This can happen when we want to use a getter for some entity attribute, and such a getter requires a parameter.

For this transformation, we can use a `ResolverMap` object .
`ResolverMap` is a Symfony service that implements `Overblog\GraphQLBundle\Resolver\ResolverMapInterface`.
ResolverMap can be created as a child of `Overblog\GraphQLBundle\Resolver\ResolverMap` class too and override the `map` method.

Example of `ResolverMap`:

```php
class CategoryResolverMap extends ResolverMap
{
    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Category' => [
                'seoH1' => function (Category $category) {
                    return $category->getSeoH1($this->domain->getId());
                },
                'seoTitle' => function (Category $category) {
                    return $category->getSeoTitle($this->domain->getId());
                },
                'seoMetaDescription' => function (Category $category) {
                    return $category->getSeoMetaDescription($this->domain->getId());
                },
            ],
        ];
    }
}
```

Each resolver map must be tagged with the `overblog_graphql.resolver_map` tag.

```yaml
# config/services.yaml
services:
    App\Model\FrontendApi\Resolver\CategoryResolverMap:
        tags:
            - { name: overblog_graphql.resolver_map, schema: default }
```

You can read more info about `ResolveMap` in [documentation](https://github.com/overblog/GraphQLBundle/blob/master/docs/definitions/resolver-map.md).

#### ProductResolverMap

Data for products can be obtained in two ways – from the Elasticsearch (for example, a single product and list of products), or from the database (for example, promoted products)

For this reason, it's necessary to know how to map fields based on the type of the result.
When a client requests any Product related field, `ProductResolverMap` checks the type of data returned from the resolver and uses appropriate field mapper from the `Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper` namespace:

- `ProductEntityFieldMapper` if resolver returns the entity `Product`
- `ProductArrayFieldMapper` if resolver returns array of values

Value for the field is resolved by one of the previously mentioned field mapper classes, with one of the methods with the specific name:

- `get<FieldName>` – field `sellingDenied` use the method named `getSellingDenied()`
- `is<FieldName>` – field `sellingDenied` use the method named `isSellingDenied()`
- `<fieldName>` – field `sellingDenied` use the method named `sellingDenied()`

Methods are searched in the order above and if the corresponding method does not exist, resolving falls back to the default (see `Overblog\GraphQLBundle\Resolver\FieldResolver` class).

### Overriding GraphQL Input Validation

When you define a `validation` section in your project's GraphQL type that inherits from a frontend-api bundle decorator, the validation array is **replaced entirely**, not merged.
Your custom constraints will only work if you also include all the decorator's original constraints.

!!! warning

    FrontendApi constraints must be listed first, before your custom ones.

```yaml
# project-base/app/config/graphql/types/.../YourInput.types.yaml
YourInput:
    type: input-object
    inherits:
        - 'FrontendApiInputDecorator'
    config:
        validation:
            # All original decorator constraints FIRST
            - Shopsys\FrontendApiBundle\Component\Constraints\OriginalConstraint: ~
            # Your custom constraints AFTER
            - App\Component\Constraints\YourCustomConstraint: ~
```

!!! note

    Check the corresponding decorator file in `packages/frontend-api/src/Resources/config/graphql-types/` to see which constraints you need to include.

#### Removing or Replacing Validation Constraints

If you want to remove or replace a validation constraint from the decorator, you can disable it by assigning a non-existent validation group.
This effectively skips the constraint because the group will never be active during validation.

```yaml
# app/config/graphql/types/.../YourInput.types.yaml
YourInput:
    type: input-object
    inherits:
        - ' FrontendApiInputDecorator'
    config:
        validation:
            # All original decorator constraints FIRST
            ...
            # Disable the original constraint by assigning a non-existent group
            - Shopsys\FrontendApiBundle\Component\Constraints\OriginalConstraint:
                groups: "removed-validation"
            # Add your replacement constraint (or omit if you just want to remove validation)
            - App\Component\Constraints\YourReplacementConstraint: ~
```

!!! tip

    Using a descriptive group name like `removed-validation` makes it clear that the constraint is intentionally deactivated.
