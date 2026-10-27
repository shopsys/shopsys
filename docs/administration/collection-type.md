# Collections with Add and Remove Buttons

`InteractiveCollectionType` is a `CollectionType` rendered in the administration with buttons for adding and removing entries, so you do not need to write any JavaScript or form theme.
The behaviour is provided by the `interactive-collection` Stimulus controller from `shopsys/administration`, the markup by the `@ShopsysAdministration/partial/_interactive_collection.html.twig` template.

[TOC]

## Basic usage

Use `InteractiveCollectionType` instead of `CollectionType`:

```php
use Shopsys\FrameworkBundle\Form\InteractiveCollectionType;

$builder->add('mailWhitelist', InteractiveCollectionType::class, [
    'entry_type' => TextType::class,
    'entry_options' => ['label' => false],
    'add_label' => t('Add another mail whitelist pattern'),
    'empty_message' => t('Add some mail whitelist patterns'),
    'prototype_data' => '/@example\.com$/',
]);
```

Each entry is rendered with `form_widget()` and `form_errors()` next to a remove button, and an add button is rendered below the entries.

### Options

| Option           | Type           | Default         | Description                                                     |
| ---------------- | -------------- | --------------- | --------------------------------------------------------------- |
| `add_label`      | `string`       | `t('Add item')` | Label of the add button                                         |
| `empty_message`  | `string\|null` | `null`          | Message shown while the collection has no entries               |
| `allow_add`      | `bool`         | `true`          | Allows adding entries, the add button is rendered               |
| `allow_delete`   | `bool`         | `true`          | Allows removing entries, the remove buttons are rendered        |
| `error_bubbling` | `bool`         | `false`         | Errors of the collection are displayed at the collection itself |

All other `CollectionType` options work as usual, especially:

- `prototype_data` sets the default value of a newly added entry,
- `prototype_name` is passed to the controller, so a custom placeholder works too,
- a `disabled` collection is rendered without any buttons.

## Customizing the look

To change how the entries look, give the collection its own block prefix and embed the shared template in its widget block, overriding only the blocks you need:

```php
$builder->add('flags', InteractiveCollectionType::class, [
    'entry_type' => PromoCodeFlagType::class,
    'block_prefix' => 'promo_code_flag_collection',
]);
```

```twig
{% block promo_code_flag_collection_widget %}
    {% embed '@ShopsysAdministration/partial/_interactive_collection.html.twig' %}
        {% block collection_entry %}
            {{ form_row(entry.flag) }}
            {{ form_row(entry.type) }}
        {% endblock %}
    {% endembed %}
{% endblock %}
```

| Block                      | Default content                                                       |
| -------------------------- | --------------------------------------------------------------------- |
| `collection_header`        | nothing                                                               |
| `collection_entry`         | `form_widget(entry)` and `form_errors(entry)`                         |
| `collection_entry_wrapper` | the entry row with the `collection_entry` block and the remove button |

The current entry is available as `entry`, the collection as `form`.
Newly added entries are rendered from the same blocks, so they look the same as the existing ones.

## Extending the behaviour

The controller dispatches events you can listen to from your own Stimulus controller or plain JavaScript:

| Event                            | `event.detail`     | Dispatched                    |
| -------------------------------- | ------------------ | ----------------------------- |
| `interactive-collection:added`   | `{ entry, index }` | after a new entry is appended |
| `interactive-collection:removed` | `{ entry }`        | after an entry is removed     |

```javascript
// assets/controllers/opening_hours_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    focusNewEntry({ detail: { entry } }) {
        entry.querySelector('input')?.focus();
    }
}
```

```twig
<div data-controller="opening-hours" data-action="interactive-collection:added->opening-hours#focusNewEntry">
    {{ form_widget(form.openingHours) }}
</div>
```

Adding or removing an entry also

- dispatches a bubbling `change` event, so the "Unsaved changes" info is shown,
- initializes the new entry with the legacy `Register` callbacks, so widgets driven by the framework scripts work in the new entry too.
