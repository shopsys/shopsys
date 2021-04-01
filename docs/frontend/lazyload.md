# Lazyload
Lazyload for images and iframes are implemented with [Minilazyload library](https://www.npmjs.com/package/minilazyload).

All images are lazy loaded by default. Default selector for lazyload is `[loading=lazy]`.
Supported browsers use native lazy load.
Browsers without native image lazy load support rely on the Minilazyload library and a placeholder image is rendered in a `src` attribute to prevent an actual image from loading until necessary.

## Disable lazyload
Lazyload might be disabled or enabled globally by defining parameter `shopsys.image.enable_lazy_load` in [`parameters_common.yaml`](https://github.com/shopsys/shopsys/blob/master/project-base/app/config/parameters_common.yaml)

Slick component is used as homepage slider for cycling through images. We don't want to use lazy loading for these images. Slick has own lazyload function. We can disable minilazyload function by setting attribute `lazy: false` in img tag.

```twig
{{ image(item, { lazy: false }) }}
```
