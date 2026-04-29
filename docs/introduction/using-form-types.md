# Using Form Types

In this article we will show what types you can use when creating or editing forms, what options they have and what they do.

We use two types of naming for form types:

- `*FormType` - This type is used in Controllers to create forms and their views on front-end or they are used in other FormTypes as sub-forms
- `*Type` - This type is used only in other FormTypes to ease adding fields with their own widgets and to add to their reusability

## Default options

Every form type has some default options that can be used for various things.

### renders_in_own_card

Controls how form fields are grouped into visual cards in the administration interface.

#### Default value

- `false` for most form types
- `true` for `GroupType` and other specialized container types

#### Behavior

**When set to `false` (default)**:

Fields are automatically grouped with adjacent fields into shared "auto cards".
Consecutive fields without this option will be rendered together in a single card container.

**When set to `true`**:

The field breaks the auto-grouping sequence and is rendered separately.
For types like `GroupType`, this means they render in their own styled card section.

#### Visual impact

In the administration interface, auto cards are rendered as Bootstrap card components (`<div class="card mb-3">`).
This automatic grouping helps organize forms visually without requiring manual card creation.

#### When to use

You typically don't need to set this option explicitly, as the defaults work well:

- Use `GroupType` when you want a labeled section in its own card
- Regular fields will automatically group together visually
- Only override this option if you need custom grouping behavior

## Form types

We created some form types which can help you with creating your own form types.
Here you can find an information about what they do and what options they have.

### [GroupType]({{github.link}}/packages/framework/src/Form/GroupType.php)

`GroupType` is used for creating groups of containers.
It is not mapped onto any property and it inherits data into group so you can work with forms in this group the same way as you were before.
`GroupType` makes sure to render your fields into nicely styled `div` wrapper.
`GroupType` comes with a few options that you can use for even more comfortable work.

#### label

This option is used for displaying heading of section, for example, in `CustomerFormType` all user data like name, last name or email address are all in section with label `Personal Data`.

### [DisplayOnlyCustomerType]({{github.link}}/packages/framework/src/Form/DisplayOnlyCustomerType.php)

Displays name of a registered customer along with a link to his/her detail.
If there is no customer set, `unregistered customer` text will be displayed instead.

### [DisplayOnlyType]({{github.link}}/packages/framework/src/Form/DisplayOnlyType.php)

Sometimes form needs to only display information but does not need to change and persist this data, for this usages
there is `DisplayOnlyType` which does not map property onto `entity` and let you to display your own data.

### [DisplayOnlyUrlType]({{github.link}}/packages/framework/src/Form/DisplayOnlyUrlType.php)

Displays custom URL based on routing system.

### [OrderItemsType]({{github.link}}/packages/framework/src/Form/OrderItemsType.php)

Displays editable table of `OrderItems` from provided `Order`.

### [MessageType]({{github.link}}/packages/framework/src/Form/MessageType.php)

Displays highlighted message with an icon. Allows set the info or warning formatting using the `message_level` option.

### [LocalizedType]({{github.link}}/packages/framework/src/Form/Locale/LocalizedType.php)

Compound type that renders one form of given type for each locale.
Returns array indexed by locale.

#### entry_type

Defaults to `TextType::class`.  
It’s used to define what FormType should be used for the inner forms.

#### entry_options

An array of options that is used for every inner form.

#### main_constraints

An array of constraints that is used for field with the same locale as administration.

### [CategoriesType]({{github.link}}/packages/framework/src/Form/CategoriesType.php)

Displays a lazily loaded tree of categories for the given `domain_id` with checkboxes for selecting categories.
Submitted checkbox values are transformed to an array of selected `Category` entities.

#### domain_id

Required option that defines for what domain should the categories be listed.

#### product

Optional `Product` used to display the current main category path above the tree in product-related forms.

### [ColorPickerType]({{github.link}}/packages/framework/src/Form/ColorPickerType.php)

Displays text field with box of given color that shows color picker when clicked.

### [DatePickerType]({{github.link}}/packages/framework/src/Form/DatePickerType.php)

Displays field that shows date picker when clicked.  
Value is internally converted from [admin display timezone](./working-with-date-time-values.md) to UTC, so it have to be persisted in database also as `DateTime` to avoid possible date shifting.

#### format

Defaults to `DatePickerType::FORMAT_PHP`.  
Defines in what format should be the date shown.
DatePickerType has 2 constants that can be used:

- FORMAT_PHP = 'dd.MM.yyyy'
- FORMAT_JS = 'dd.mm.yy'

### [DateTimeType]({{github.link}}/packages/framework/src/Form/DateTimeType.php)

Displays a text field that allows to set date and time.  
Value is internally converted from [admin display time zone](./working-with-date-time-values.md) to UTC.

#### format

Defaults to `DateTimeType::FORMAT_PHP`.  
Defines in what format should be the date shown.
DatePickerType has constant that can be used:

- FORMAT_PHP = 'dd.MM.yyyy HH:mm:ss'

### [DisplayOnlyDomainIconType]({{github.link}}/packages/framework/src/Form/DisplayOnlyDomainIconType.php)

Displays domain icon with the domains name for given domain ID in `data` option.

### [DomainsType]({{github.link}}/packages/framework/src/Form/DomainsType.php)

Displays list of non-required checkboxes for every domain with the domain name as label.

### [DomainType]({{github.link}}/packages/framework/src/Form/DomainType.php)

Displays select box with all domain names or domain URLs.

#### displayUrl

Defaults to `false`.  
If you set this option to `true`, domain url will be shown instead of domain name.

#### limit_domains_by_ids

Limits list of displayed domains to provided ids.

### [FileUploadType]({{github.link}}/packages/framework/src/Form/FileUploadType.php)

Uses `AbstractFileUploadType` to display a widget for files upload and to work with them (similar as `ImageUploadType`).
After a file or files are uploaded it shows box for every file and lets you to downloads, order or delete files.

#### file_constraints

An array of constraints that should be applied for the uploaded file.

#### file_entity_class

Defines which entity class (defined in [uploaded_files.yaml]({{github.link}}/project-base/config/uploaded_files.yaml)) should the files be assigned to.

#### file_type

Defines which type of file (defined in [uploaded_files.yaml]({{github.link}}/project-base/config/uploaded_files.yaml)) should the files be assigned to.

#### info_text

Required option that needs to be string or null and is a text that is shown under the upload icon.

#### entity

Defines which entity should the files be assigned to.

### [BasicFileUploadType]({{github.link}}/packages/framework/src/Form/BasicFileUploadType.php)

Uses `AbstractFileUploadType` to display a widget for file uploads and manage them without associating them with any entity.

#### multiple

Allows multiple files to be uploaded.

#### with_names_inputs

Allows to input custom filenames and translated names for uploaded files.

### [FriendlyUrlType]({{github.link}}/packages/framework/src/Form/FriendlyUrlType.php)

Displays a select box with domain urls and text field that lets you to create friendly url on selected domain with your valid slug.
Uses `DomainType` to display select box with domain urls.

#### limit_domains_by_ids

Limits list of displayed domains to provided ids.

### [UrlListType]({{github.link}}/packages/framework/src/Form/UrlListType.php)

Uses `FriendlyUrlType` to display a list of friendly URLs for each domain that lets you delete and create friendly URLs with unique slugs and select which URL should be the main for the domain.

#### route_name

Defines which route should the URLs go into.

#### entity_id

Defines what is the entity ID that the URLs are assigned to.

#### limit_domains_by_ids

Limits list of displayed domains to provided ids.

### [ImageUploadType]({{github.link}}/packages/framework/src/Form/ImageUploadType.php)

Uses `AbstractFileUploadType` to display a widget for images upload and to work with them (same as `FileUploadType`).

#### entity

Defines which entity should the images be assigned to.

### [OrderListType]({{github.link}}/packages/framework/src/Form/OrderListType.php)

Displays a list of orders for given `user`.
The list contains `order number`, `created on`, `billing address`, `delivery address`, `total price including VAT`, `status` and a link to order detail.
Displays text `Customer has no orders so far.` if User doesn't have any orders.

#### user

Required option that defines for what user should the orders be listed.

### [PhoneType]({{github.link}}/packages/framework/src/Form/PhoneType.php)

Compound form type for phone number input consisting of a country dial code selector and a phone number field.
Data class is `PhoneData` which holds `countryCode` (ISO 3166-1 alpha-2), `prefix` (dial code, e.g. `+420`), and `number`.

Sub-fields:

- **`countryCode`** — `ChoiceType` listing all available dial codes. Codes enabled for the given `domain_id` are shown as preferred choices at the top of the list. Each option label shows the country flag emoji, dial code, and country name.
- **`number`** — `TelType` for the phone number itself.

On submit, `prefix` is automatically derived from the selected `countryCode`.
On data initialization, if only `prefix` is stored (no `countryCode`), the matching `countryCode` is resolved automatically. If `countryCode` is stored but does not match the stored `prefix`, `countryCode` is cleared.

The view exposes an `unknownPrefix` variable containing the stored prefix value when it does not correspond to any known dial code (useful for displaying a warning or fallback).

The type always applies `PhoneNumber` and `PhoneNumberPrefixConsistency` constraints.

#### Options

##### domain_id

Required `int` option. Used to determine which dial codes should be highlighted as preferred choices (those enabled on the given domain).

##### required

Defaults to `true`.
When `true`, both `countryCode` and `number` sub-fields have `NotBlank` constraints applied.

#### Overriding Phone Number validation in project

`PhoneType` uses the `PhoneNumber` constraint and `PhoneNumberValidator`.
If you need project-specific validation behavior, you can override the validator by extending the validator and rebinding its service ID.

Example:

```php
<?php

declare(strict_types=1);

namespace App\Component\Validator;

use Shopsys\FrameworkBundle\Form\Constraints\PhoneNumber;
use Shopsys\FrameworkBundle\Form\Constraints\PhoneNumberValidator as BasePhoneNumberValidator;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

class PhoneNumberValidator extends BasePhoneNumberValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        // You can add custom validation logic here, for example:
        // - Validate the phone number against specific country formats
        // - Check for allowed prefixes using PhoneData
        // - Add custom error messages based on your requirements

        // Call the parent validator to maintain existing validation behavior if necessary
        parent::validate($value, $constraint);
    }
}
```

```yaml
# project-base/app/config/services.yaml
services:
    Shopsys\FrameworkBundle\Form\Constraints\PhoneNumberValidator:
        class: App\Component\Validator\PhoneNumberValidator
```

### [ProductsType]({{github.link}}/packages/framework/src/Form/ProductsType.php)

Displays a list of products.
The widget adds button that after clicking opens a popup window that lets you to search and to pick products except for `main_product`.
After picking you can sort or delete picked products.

#### allow_add

Boolean option that defines if products can be added to the list.

#### allow_delete

Boolean option that defines if products can be removed from the list.

#### sortable

Boolean option that defines if products in the list can be sorted.

#### allow_main_variants

Boolean option that defines if main variants can be added.

#### allow_variants

Boolean option that defines if variants can be added.

#### main_product

Option that defines for what product are you picking products. `main_product` can't be picked.

#### label_button_add

Option that defines text of button for adding products.

### [ProductType]({{github.link}}/packages/framework/src/Form/ProductType.php)

Displays a widget that lets you to pick one product.
It has one `input` with `placeholder` or name of picked product and a `button` that after clicking opens a popup window that lets you to search and to pick a product.

#### placeholder

Option that lets you to add a placeholder text when you dont have picked product.

#### allow_delete

Boolean option that defines if product can be deleted.

#### allow_main_variants

Boolean option that defines if main variants can be picked.

#### allow_variants

Boolean option that defines if variants can be picked.

### [RolesType]({{github.link}}/packages/framework/src/Form/RolesType.php)

Displays a grid of roles with permission checkboxes for managing user permissions in administration.
The form type renders roles organized by sections (when grouped) with columns for different permission levels (VIEW, CREATE, EDIT, DELETE, or simplified VIEW/FULL).

#### context

Required option that defines which context roles should be displayed from.
Must be a class-string of a context (e.g., `AdminContext::class`).
For more information about contexts, see [Context System](context-system.md).

#### excluded_roles

Defaults to all system roles (`SystemRole::getAll()`).  
Array of role constants that should be excluded from the display.
Typically used to exclude system roles like `ROLE_ALL`, `ROLE_ALL_VIEW`, etc.

#### simple_permissions

When `true`, only displays VIEW and FULL permissions.
When `false`, displays all granular permissions (VIEW, CREATE, EDIT, DELETE).

#### grouped

Defaults to `true`.  
When `true`, roles are organized into sections (e.g., "Orders & Customers", "Products & Catalog").
When `false`, all roles are displayed in a single ungrouped list.

#### Example Usage

```php
$builder->add('roles', RolesType::class, [
    'context' => AdminContext::class,
    'excluded_roles' => [SystemRole::ALL, SystemRole::ALL_VIEW],
    'simple_permissions' => true,
    'grouped' => true,
]);
```

### [SingleCheckboxChoiceType]({{github.link}}/packages/framework/src/Form/SingleCheckboxChoiceType.php)

Displays a list of choices shown as checkboxes.

### [SortableValuesType]({{github.link}}/packages/framework/src/Form/SortableValuesType.php)

Displays a list of values that lets you add values from a select box, remove them form the list or sort them as you like.
Returns array with sorted IDs that have been picked and sorted.

#### allow_add

Boolean option that defines if products can be added to the list.

#### allow_delete

Boolean option that defines if products can be removed from the list.

### [MultidomainType]({{github.link}}/packages/form-types-bundle/src/MultidomainType.php)

Compound type that renders one form of given type for each domain.
The data of the inner forms are returned as an array indexed by the domain ID.

#### entry_type

Defaults to `TextType::class`.  
The type of the inner form.

#### entry_options

Defaults to `[]`.  
The options of the inner forms.

#### options_by_domain_id

Defaults to `[]`.  
The options of the inner forms based on the domain ID.
Provide arrays indexed by the domain ID, values are merged with the `entry_options`.

### [YesNoType]({{github.link}}/packages/form-types-bundle/src/YesNoType.php)

Natural looking choice type for boolean value inputs.
A boolean value is accepted/returned as data.
A null value can be accepted/returned when no radio button is checked.

### [ActionBarType]({{github.link}}/packages/form-types-bundle/src/ActionBarType.php)

The `ActionBarType` form type provides a standardized action bar, rendered at the bottom of the page, with the "Back" link and the "Save" button.

#### Available Options

| Option       | Type                          | Default Value           | Description                                                                                       |
| ------------ | ----------------------------- | ----------------------- | ------------------------------------------------------------------------------------------------- |
| `back_route` | <code>string&#124;null</code> | `null`                  | Symfony route name for the back button. Cannot be used with `back_url`.                           |
| `back_url`   | <code>string&#124;null</code> | `null`                  | Absolute or relative URL for the back button. Cannot be used with `back_route`.                   |
| `back_label` | `string`                      | `t('Back to overview')` | Label for the back button.                                                                        |
| `save_label` | <code>string&#124;null</code> | `null`                  | Label for the save button. Defaults to `"Save changes"` if `entity` is set, otherwise `"Create"`. |
| `entity`     | <code>object&#124;null</code> | `null`                  | The entity associated with the form. Used to determine the save button label.                     |
| `mapped`     | `bool`                        | `false`                 | Always set to `false`.                                                                            |

#### Constraints

- If `back_route` is set, `back_url` must be `null`, and vice versa.

#### Form Fields

##### `back_link` (Optional)

- **Type**: `LinkType`
- **Added when**: Either `back_route` or `back_url` is set
- **Label**: Set from `back_label`

##### `save`

- **Type**: `SubmitType`
- **Label**:
    - `"Save changes"` if `entity` is not `null`
    - `"Create"` otherwise
    - Can be overridden using `save_label`

#### Example Usages

##### Most common usage

```php
final class MyEntityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => t('Name')])
            ->add('actionBar', ActionBarType::class, [
                'back_route' => 'admin_myentity_list',
                'entity' => $options['my_entity'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('my_entity')
            ->setAllowedTypes('my_entity', [MyEntity::class, 'null'])
            ->setDefaults([
                'data_class' => MyEntityData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
```

##### Adding another item to the action bar

```php
$actionBar = $builder->create('actionBar', ActionBarType::class, [
    'back_route' => 'admin_myentity_list',
    'entity' => $options['my_entity'],
]);

$actionBar->add('createAndDownloadCsv', SubmitType::class, [
    'label' => 'Create and download CSV',
    'position' => [
        'before' => 'save',
    ],
]);

$builder->add($actionBar);
```

!!! note

    For information about `position` option see [FormExtension](../extensibility/form-extension.md#changing-order-of-groups-and-fields)

## Form Input Symbols

The administration form theme provides a convenient way to add symbols or text before or after form input fields using Bootstrap input groups.
This feature works with any form widget that extends the `form_widget_simple` block.

### Available Symbol Options

| Option                 | Description                                | HTML Escaping                 |
| ---------------------- | ------------------------------------------ | ----------------------------- |
| `symbolAfterInput`     | Adds text or symbol after the input field  | Escaped (safe for plain text) |
| `symbolBeforeInput`    | Adds text or symbol before the input field | Escaped (safe for plain text) |
| `rawSymbolAfterInput`  | Adds HTML content after the input field    | Unescaped (allows HTML)       |
| `rawSymbolBeforeInput` | Adds HTML content before the input field   | Unescaped (allows HTML)       |

### Usage Examples

#### Currency Symbols

```twig
{{ form_widget(form.price, { symbolAfterInput: currencySymbolByDomainId(domainId) }) }}
```

#### Unit Symbols

```twig
{{ form_widget(form.vatPercent, { symbolAfterInput: '%' }) }}
{{ form_widget(form.weight, { symbolAfterInput: 'g'|trans }) }}
```

#### HTML Icons (using raw variants)

```twig
{{ form_widget(form.field, { rawSymbolAfterInput: info_icon('Help text') }) }}
```

#### Multiple Symbols

```twig
{{ form_widget(form.priceRange, {
    symbolBeforeInput: '$',
    symbolAfterInput: 'USD'
}) }}
```
