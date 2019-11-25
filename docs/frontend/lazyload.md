# Lazyload
Lazyload for images and iframes are implemented with [Minilazyload library](https://www.npmjs.com/package/minilazyload).

All images are lazy loaded by default. Default selector for lazyload is `[loading=lazy]`.
Native lazy load is overrided - we are waiting for bigger browser support.

## Disable lazyload
Slick component is used as homepage slider for cycling through images. We don't want to use lazy loading for these images. Slick has own lazyload function. We can disable minilazyload function by setting attribute `lazy: false` in img tag.

```twig
{{ image(item, { lazy: false }) }}
```

### Manual lazyload call
If you need lazy loading in some situations (like popup menu with images) call lazyload function manually, or if you want load images on ajax content, you can use function:

```javascript
Shopsys.lazyLoadCall.inContainer(container);
```

As container variable use CSS selector for container with images. All images in this container will be loaded after function call.
