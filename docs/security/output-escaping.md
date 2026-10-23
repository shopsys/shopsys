# Output Escaping in Administration

[TOC]

Twig escapes every `{{ value }}` automatically, so a value printed directly in a template is safe.
XSS appears when HTML is composed **outside** the template (in a controller, a service, or a translation) and the template then prints it with `|raw`.
Any value an administrator or a customer can store (entity names, labels, e-mails, file names, domain names) must be escaped before it becomes part of such HTML,
otherwise one administrator can store a script that runs in the browser of another administrator.

The rule of thumb: **never put a dynamic value into an HTML string through `t()` parameters, `sprintf()`, or string concatenation.**
Pass it separately to a helper that escapes it, as described below.

## Flash messages

Flash messages are rendered raw, so [`FlashMessageService`]({{github.link}}/packages/framework/src/Component/FlashMessage/FlashMessageService.php) (and `FlashMessageTrait` in controllers) offers two kinds of methods:

| Method                                                                                        | Message                                  | Dynamic values                                               |
| --------------------------------------------------------------------------------------------- | ---------------------------------------- | ------------------------------------------------------------ |
| `addSuccessFlash()`, `addErrorFlash()`, `addInfoFlash()`, `addWarningFlash()`                 | plain text, the whole message is escaped | `t()` parameters are fine, they are escaped with the message |
| `addSuccessFlashTwig()`, `addErrorFlashTwig()`, `addInfoFlashTwig()`, `addWarningFlashTwig()` | Twig template source, may contain HTML   | template parameters (second argument), escaped by Twig       |

Use the `*FlashTwig()` variant only when the message really contains HTML, and pass dynamic values as template parameters:

```php
$this->addSuccessFlashTwig(
    t('Holiday / internal day <strong>{{ name }}</strong> deleted'),
    ['name' => $closedDayName],
);
```

Never put a value into the template string itself — neither through `t()` parameters nor by concatenation.
The string is compiled as a Twig template, so a stored value like `{{ app.request.server.all }}` would be evaluated as Twig code (server-side template injection), and HTML in it would not be escaped.

```php
// WRONG — $closedDayName becomes part of the template source
$this->addSuccessFlashTwig(t('Holiday / internal day <strong>{{ name }}</strong> deleted', ['{{ name }}' => $closedDayName]));
```

## Delete confirmations

[`ConfirmDeleteResponseFactory`]({{github.link}}/packages/framework/src/Component/ConfirmDelete/ConfirmDeleteResponseFactory.php) escapes a message passed as a string, so a plain-text confirmation is safe even with dynamic values in `t()` parameters.
When the message needs markup (`<br>`, `<strong>`), pass it as `Twig\Markup`, translate it **without** the dynamic values and pass them in the `$messageParameters` argument, where they are escaped and replaced in the message:

```php
$message = new Markup(t('Do you really want to remove unit "%name%"?<br>It is not used anywhere.'), 'UTF-8');

return $this->confirmDeleteResponseFactory->createDeleteResponse(
    $message,
    'admin_unit_delete',
    $id,
    ['%name%' => $unit->getName()],
);
```

`createSetNewAndDeleteResponse()` accepts `$messageParameters` the same way.

Confirmations of datagrid row actions in CRUD controllers follow the same contract: `RowAction::setConfirmMessage()` escapes a string message and accepts the dynamic values as its second argument.
To compute the message from the row data, set it in a callback:

```php
$datagrid->actions()->update(
    'delete',
    fn (RowAction $rowAction) => $rowAction->addCallback(
        fn (array $row, RowAction $action) => $action->setConfirmMessage(
            t('Do you really want to remove sales representative "%label%" permanently?'),
            ['%label%' => $row['label']],
        ),
    ),
);
```

- Placeholders that are left out of `t()` parameters stay in the translated message, so the factory can replace them afterwards.
- Values that only select the plural form or are generated numbers (for example `%count%`) can stay in `t()` parameters.
- A parameter passed as `Twig\Markup` is inserted unescaped, so you can pass HTML you composed yourself (for example a list of e-mails joined with `<br>`), as long as you escape each value in it with `EscapedMessageInterpolator::escape()`.
- Values passed to `|trans()` inside a `data-confirm-message` attribute in a template must be escaped with `|e`, because the confirm window renders the attribute content as HTML:

```twig
data-confirm-message="{{ 'Do you really want to change order status to <strong>%s</strong>?'|trans({'%s': status.name|e}) }}"
```

## HTML composed in PHP

When a service or a Twig extension builds an HTML string that is later printed with `|raw`
(for example [`RequiredSettingExtension`]({{github.link}}/packages/framework/src/Twig/RequiredSettingExtension.php) that composes the warnings shown on every administration page),
insert the dynamic values with [`EscapedMessageInterpolator`]({{github.link}}/packages/framework/src/Component/Html/EscapedMessageInterpolator.php), which escapes them:

```php
$message = $this->escapedMessageInterpolator->interpolate(
    t('Warehouse <a href="%url%">%stockName%</a> is set as default on domain "%domainName%" but is not enabled there.'),
    [
        '%url%' => $this->router->generate('admin_stock_edit', ['id' => $stockId]),
        '%stockName%' => $stockName,
        '%domainName%' => $domainConfig->getName(),
    ],
);
```

When the HTML is concatenated (for example a list of items built in a loop), escape each value with its `escape()` method.

If you can, prefer rendering such HTML in a Twig template, where escaping is automatic.

## HTML from WYSIWYG editors

Texts edited in CKEditor are HTML by design, so they cannot be escaped.
Do not print them with `|raw`; sanitize them with the same sanitizer the CKEditor preview uses:

```twig
{{ value|sanitize_html('shopsys.ckeditor_preview') }}
```
