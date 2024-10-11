# Disabling form fields

During project implementation, there is usually necessary to have imported some fields from other systems.
Those imported fields do not need (or even must not) be changeable in administration.
For this purpose, there is implemented the way how to define fields which should be disabled.

## Enable form disabling

To enable disabling defined fields there, need to be set ENV variable `DISABLE_FORM_FIELDS_FROM_TRANSFER` to true.

## Define disabled fields

Disabled fields are defined by constant `DISABLED_FIELDS` for example in: `App\Form\Admin\CategoryFormTypeExtension`
