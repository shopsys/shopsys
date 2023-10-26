# Icon Function

Icon twig function allows you to easily change icons html throughout the application.
It is default for svg font icons.
It also allows you to change or add new types without re-writing whole frontend templates.

## Usage

```twig
{{ icon('warning') }}
```

## Params

-   name - first param is name of icon
-   class - optional - adds html attribute class with string inside (you can see above)
-   type - optional - is used type (svg-font (default), image, svg-sprite, svg-inline)

## How to define new type

Override template from framework `src/Resources/Common/Inline/Icon/icon.html.twig` and copy or edit first condition with all content.
There you can see loop that brings you ability to add data-\* attributes to icon.

## How to pass params to icon element

```twig
{{ icon('arrow', {
    class: 'cursor-pointer js-arrow-icon',
    data: { show-on-mobile: 'true'},
    title: 'Arrow'
})}}
```
