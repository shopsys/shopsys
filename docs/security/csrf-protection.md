# CSRF Protection

[TOC]

Cross-site request forgery (CSRF) protection makes sure a state-changing request really came from a page rendered by the application, not from a third-party site.

## Route-level protection: `#[CsrfProtection]` and `protectedUrl()`

These two are two halves of one mechanism, and you always need both:

| Piece                          | Where             | What it does                                                                                      |
| ------------------------------ | ----------------- | ------------------------------------------------------------------------------------------------- |
| `#[CsrfProtection]`            | controller method | **Enforces**: the request must carry a valid `routeCsrfToken`, otherwise the response is HTTP 400 |
| `protectedUrl('route', {...})` | Twig template     | **Supplies**: generates the URL like `url()` and appends a valid `routeCsrfToken` parameter       |

Using only one of them is always a bug:

- `#[CsrfProtection]` without `protectedUrl()` — every link to the action ends with `400 Bad Request` (`Csrf token is invalid`).
- `protectedUrl()` without `#[CsrfProtection]` — the token is appended but never checked, so the action is **not protected**.

The token is bound to the route name, not to the entity, and is tied to the user's session like a form token.
Implementation lives in [`RouteCsrfProtector`]({{github.link}}/packages/framework/src/Component/Router/Security/RouteCsrfProtector.php)
and [`CsrfExtension`]({{github.link}}/packages/framework/src/Twig/CsrfExtension.php).

### Standard usage

1. Mark the controller action:

    ```php
    use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;

    #[Route(path: '/store/setdefault/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit]
    #[CsrfProtection]
    public function setDefaultAction(int $id): Response
    ```

2. Generate every link to it with `protectedUrl()` (same arguments as `url()`):

    ```twig
    <a href="{{ protectedUrl('admin_store_setdefault', { id: store.id }) }}" class="btn btn-secondary">
        {{ 'Set as default'|trans }}
    </a>
    ```

The attribute also works on invokable controllers (`__invoke`).

### Places where the token is added for you

Do not call `protectedUrl()` or compose the token yourself when the link is produced by one of these:

- **Grid action columns** of type `delete` and `resetPassword` —
  [`ActionColumn`]({{github.link}}/packages/framework/src/Component/Grid/ActionColumn.php) appends the token unconditionally.
  Other column types (for example `edit`) never get a token.
- **CRUD Controller actions** (`action_url()` Twig function, `linkToRoute()`) —
  [`ActionExtension`]({{github.link}}/packages/administration/src/Twig/ActionExtension.php) appends the token whenever the target action has `#[CsrfProtection]`.
  See [CRUD Controller actions](../administration/crud-controller/reference/actions.md).
- **Confirm-delete modals** rendered by
  [`ConfirmDeleteResponseFactory`]({{github.link}}/packages/framework/src/Component/ConfirmDelete/ConfirmDeleteResponseFactory.php).

### Passing the token manually

Use this only when the URL is not built by `protectedUrl()` or the helpers above (for example a form action or a URL built in PHP).

In Twig, use the same token id the protector expects:

```twig
{% set csrfTokenId = constant('Shopsys\\FrameworkBundle\\Component\\Router\\Security\\RouteCsrfProtector::CSRF_TOKEN_ID_PREFIX') ~ 'admin_category_delete' %}
{% set deleteUrl = url('admin_category_delete', {
    id: category.id,
    (constant('Shopsys\\FrameworkBundle\\Component\\Router\\Security\\RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER')): csrf_token(csrfTokenId)
}) %}
```

In PHP, inject `RouteCsrfProtector` and use `getCsrfTokenByRoute()`:

```php
$routeParams = [
    'id' => $adminToEdit->getId(),
    RouteCsrfProtector::CSRF_TOKEN_REQUEST_PARAMETER => $this->routeCsrfProtector->getCsrfTokenByRoute('admin_administrator_send-reset-password'),
];
```

The parameter may be sent in the query string or as a POST field — both are accepted.

### Smoke tests

The HTTP smoke tests do not know about the attribute. A new protected route fails with `400` unless its name matches the pattern in
[`RouteConfigCustomization`]({{github.link}}/project-base/app/tests/App/Smoke/Http/RouteConfigCustomization.php)
(`*_delete`, `*_setdefault`, `*_deleteconfirm`, ...) or you add a `customizeByRouteName()` entry that calls `createAddCsrfTokenDuringTestExecutionCallback()`.

## Symfony form CSRF

`csrf_protection` is enabled globally in
[`project-base/app/config/packages/framework.yaml`]({{github.link}}/project-base/app/config/packages/framework.yaml),
so every form gets a hidden `_token` field and is validated on submit. You do not need `#[CsrfProtection]` on an action that only handles a form.

Disable it (`'csrf_protection' => false` in `configureOptions()`) only for GET search/filter forms that do not change state, for example
[`QuickSearchFormType`]({{github.link}}/packages/framework/src/Form/Admin/QuickSearch/QuickSearchFormType.php) or
[`AdvancedSearchFormFactory`]({{github.link}}/packages/framework/src/Model/AdvancedSearch/AdvancedSearchFormFactory.php).
