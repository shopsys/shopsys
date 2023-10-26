# Form Extension

In this document, we will be explaining the actual state of our forms and their extensions. Right now,
solution of extending forms is not complete and there will be several tasks that will ensure better extending.

At this time, we do not have every single form in our application ready for extension, the list of not prepared
forms for extensions is below:

-   `ProductMassActionFormType`
-   `VariantFormType`
-   `OrderItemFormType`
-   `OrderPaymentFormType`
-   `OrderTransportFormType`

If you want to see an example of extending one of these forms, please check this [link](https://github.com/shopsys/shopsys/commit/d6b84bf54c0b47c72eacc82d540987dd8078fa13).

## What can we use for creating our own forms

We created some form types that can help you create your own. You can find them in [separate article](../introduction/using-form-types.md)

## Adding fields into already existing form types in administration

Imagine that you have added a new property into `Product entity` and you want this property to be set in administration
using forms.

For this case, you can use `FormExtensions` in the namespace `App/Form/Admin` that extends `Symfony\Component\Form\AbstractTypeExtension`, which has a function called `getExtendedType()`.
Implement this function and return `class` of `ProductFormType` and add your fields into the form.
If you create new extension you need to register it in [`config/forms.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/config/forms.yaml)

## Changing rendering of already existing form type

If you want to change the way the form is rendered or if you want to add your own classes, you need to follow a few steps.
Many of our forms have their own theme, which describes how to render form row. These files are located in `Shopsys/FrameworkBundle/Resources/views/Admin/Form` folder.
Copy the theme of the form type you want to change into your project namespace and replace the file you want to
change with your own file in `twig/form_themes` in `config/packages/twig.yaml` file. Now you can change whatever you want.

Remember that files you copy into your project cannot be automatically upgraded with newer versions of Shopsys Platform.

If you want to change the whole style of rendering forms in administration, you need to copy the whole [`theme.html.twig`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Resources/views/Admin/Form/theme.html.twig) which defines the style of
rendering default Symfony rows.
You can read more about `theme.html.twig` below.

## Adding your own form type

You can add your own form type if you want. Just create your own FormType, for example, `MyAmazingFormType`, if you want
to influence how this form type will be displayed, you need to create a theme for this form type.

Create a new file into `templates/Front/Form` directory, name it, for example, `myAmazingFields.html.twig` and register
this theme into `twig/form_themes` in `config/packages/twig.yaml`.

Now, you can define how the form type will be rendered. You can influence the rendering of form type in two ways.

### `form_row`

Form row is used for rendering the whole row, including the label of the form, your icons etc. `form_row` should call `form_widget`, `form_errors` and `form_label`.

### `form_widget`

The form widget defines the rendering of actual input.

Just remember that you must let `Symfony` know which form type you are defining. If you want to define
rendering of `MyAmazingFormType` your `form_widget` and `form_row` should be named `my_amazing_widget` and `my_amazing_row`.

## `theme.html.twig`

This template is used for custom rendering of forms and form fields and it extends `form_div_layout.html.twig` from Symfony.
There are two `theme.html.twig` files as one is used for [administration](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Resources/views/Admin/Form/theme.html.twig) and the other for [front-end](https://github.com/shopsys/shopsys/blob/master/project-base/templates/Front/Form/theme.html.twig).
It contains a definition of blocks that are used for rendering forms.

-   `form_start` - renders the start tag of the form
-   `form_end` - renders the end tag of the form
-   `form_row` - renders the label, any errors, and the HTML form widget for the given field
    -   `form_widget` - renders HTML form widget for the given field
    -   `form_errors` - renders block with a list of validation errors for the given field
    -   `form_label` - renders label for the given field, including red asterisk if the field is required

and blocks of custom form widgets for various [FormTypes](../introduction/using-form-types.md) e.g.:

-   `date_picker_widget` is rendered as `form_widget` for [`DatePickerType`](https://github.com/shopsys/shopsys/blob/master/packages/framework/src/Form/DatePickerType.php)

## Changing order of groups and fields

All form types contain an option called `position`. With this option, you can specify the position of your group or field **on the same hierarchical layer**.

Option `position` can contain four different values:

```php
$builder
    ->add('g', TextType::class, ['position' => 'last'])
    ->add('a', TextType::class, ['position' => 'first'])
    ->add('c', TextType::class)
    ->add('f', TextType::class)
    ->add('e', TextType::class, ['position' => ['before' => 'f']])
    ->add('d', TextType::class, ['position' => ['after' => 'c']])
    ->add('b', TextType::class, ['position' => 'first']);
```

The output will be: A => B => C => D => E => F => G.

!!! tip

    More examples can be found [here](https://github.com/shopsys/ordered-form/blob/master/doc/usage.md#position).

### Changing order of existing groups and fields

Implementation of `FormBuilderInterface` contains the method `setPosition` that can change the order of existing fields.

```php
$builder->get('a')->setPosition('first');
$builder->get('c')->setPosition(['after' => 'b']);
```

!!! note

    Because `FormBuilderInterface` doesn't declare the method `setPosition`, your IDE will warn you that method doesn't exist.
