# Lazyload
Lazyload for images and iframes are implemented with [Minilazyload library](https://www.npmjs.com/package/minilazyload).

All images are lazy loaded by default. Default selector for lazyload is [loading=lazy].
Native lazy load is overrided - we are waiting for bigger browser support.

## Disable lazyload
At homepage slider is used Slick to rotate slider images. We dont want to use lazyload on these images. Slick has own lazyload function. We can disable minilazyload function by `lazy: false` attribute in img tag.

```twig
{{ image(item, { lazy: false }) }}
```

### Manual lazyload call
If you need in some situations (like popup menu with images) call lazyload function manually, or if you want load images on ajax content, you can use function:

```javascript
Shopsys.lazyLoadCall.inContainer(container);
```

As container varibale use css selector for container with images. All images in this container will be loaded after function call.
