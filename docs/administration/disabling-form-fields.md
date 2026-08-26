# Disabling form fields

During project implementation, there is usually necessary to have imported some fields from other systems.
Those imported fields do not need (or even must not) be changeable in administration.
For this purpose, there is implemented the way how to define fields which should be disabled.

## Enable form disabling

To enable disabling defined fields there, need to be set ENV variable `DISABLE_FORM_FIELDS_FROM_TRANSFER` to true.

## Define disabled fields

Disabled fields are defined by constant `DISABLED_FIELDS` for example in: `App\Form\Admin\CategoryFormTypeExtension`

A disabled field is read-only in both directions: it is rendered without its editing controls,
and Symfony discards whatever was submitted for it, so the original value is kept even if the form is posted with the field removed or tampered with.

## Supported field types

Standard Symfony form fields render the `disabled` attribute automatically.

Custom form types that honour `disabled` on top of that:

| Form type                                                                        | Rendering when disabled                                                                                                                                                                                                      |
| -------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `TreeSelectionType` (e.g. `CategoriesType` used for product category assignment) | All checkboxes in the tree are disabled, including branches lazily loaded via AJAX; the tree can still be expanded and browsed.                                                                                              |
| `ProductsType` (product variants, accessories, related products)                 | Plain list of the assigned products with their images, names and catalogue numbers; no "Add product" button, no delete action, no drag handle, and no products picker JavaScript is attached, so drag-and-drop does nothing. |
| `RolesType`                                                                      | The role grid is rendered without its editing controls.                                                                                                                                                                      |

## Field types that do not support disabling yet

These form types render their own controls in a custom template and ignore `disabled`.
Disabling them still protects the data — submitted values are discarded by Symfony — but the administration keeps showing controls that do nothing:

- collections with hand-rolled add/remove buttons: `HreflangCollection`, `MailWhitelistCollectionType`, `OpeningHoursCollection`, `OrderItemsType`, `ComplaintItemsType`, `PriceListProductsPickerType`, `ProductParameterValue`, `ProductVideosDataCollection`, `PromoCodeFlagCollectionType`, `PromoCodeLimitCollectionType`, `TransportPricesWithLimitsCollection`
- pickers, editors and lists with their own controls: `UrlListType`, `SinglePickerType`, `InlineSinglePickerType`, `FriendlyUrlType`, `CKEditorType`, `GrapesJsType`, `GrapesJsMailType`
- `SortableValuesType` — the select is disabled, but the drag handle and the remove action of the already selected values are not
