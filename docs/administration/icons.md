# Icons

The Shopsys Platform uses the [`symfony/ux-icons`](https://symfony.com/bundles/UXIcons/current/index.html) package to render **Tabler icons** in the administration panel.
This document explains how to use icons in Twig templates and JavaScript.

[TOC]

## 1. Using Icons in Twig Templates

To render an icon inside a Twig template, use the `ux_icon()` function:

```twig
{{ ux_icon('info') }}
```

Shopsys Platform uses, by default, an alias table to map the icon names to the corresponding icon from the Tabler icon set.
The alias table is defined in the `config/packages/ux_icons.yaml` file.
This allows you to use the icon names without specifying the icon set and optionally replace the icon set in the future.

It's up to you whether you want to use aliases for your custom icons or use the icon names from the icon set directly.

### Locking Icons

Whenever a new icon is used in a Twig template, it must be locked and committed to ensure it is included in the build:

```bash
./bin/console ux:icons:lock
```

## 2. Using Icons in JavaScript

### Importing Icons in JavaScript

To use an icon in JavaScript, first import the corresponding SVG file and then include it.
Thanks to the `?raw` import query, the SVG file is automatically inlined in the JavaScript bundle.

```javascript
import InfoCircle from 'icons/tabler/info-circle-filled.svg?raw';

$('.some-element').html('<strong>' + InfoCircle + ' This is an info icon</strong>');
```

### Importing New Icons for JavaScript Only

If a new icon is required exclusively for JavaScript (and not in Twig templates), it must be explicitly imported using the import command:

```bash
./bin/console ux:icons:import tabler:alien
```

_Replace tabler:alien with the desired Tabler icon name._

Read more:

- [Tabler Icons List and Documentation](https://tabler.io/icons)
- [Symfony UX Icons Documentation](https://symfony.com/bundles/ux-icons/current/index.html)
