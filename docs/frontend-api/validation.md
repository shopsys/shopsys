# Validation in Frontend API

Shopsys Platform integrates Symfony's validation component with GraphQL through the [overblog/GraphQLBundle](https://github.com/overblog/GraphQLBundle/blob/master/docs/validation/index.md). This enables automatic validation of mutation inputs before they are processed.

## How It Works

The GraphQL bundle automatically validates input data when validation constraints are defined. When validation fails, an `ArgumentsValidationException` is thrown, which is then handled by the error subscriber to return properly formatted error responses.

For more details about Symfony validation, see the [Symfony Validation documentation](https://symfony.com/doc/current/validation.html).

## XSS Protection

Shopsys Platform provides built-in XSS protection for all GraphQL mutations through the `MutationAntiXss` constraint:

```yaml
# config/graphql/types/Mutation/Mutation.types.yaml
Mutation:
    type: object
    inherits:
        - 'MutationDecorator'
    validation:
        - 'MutationAntiXss': ~ # Applied globally to all mutations
```

### How XSS Protection Works

- The `MutationAntiXss` constraint scans all string inputs for potential XSS attacks
- It uses the [voku/anti-xss](https://github.com/voku/anti-xss) library for detection
- Certain fields are automatically excluded (IDs, tokens, passwords, UUIDs, etc.)
- Additional exclusions can be configured via the `shopsys.frontend_api.anti_xss_excluded_fields` parameter

### Configuring XSS Exclusions

To exclude specific fields from XSS validation, add them to your parameters:

```yaml
# app/config/parameters_common.yaml
parameters:
    shopsys.frontend_api.anti_xss_excluded_fields:
        - customHtml
        - richTextContent
        - jsonData
```
