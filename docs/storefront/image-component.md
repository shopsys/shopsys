# Image Component

UI component that is a custom wrapper for the [`Next/Image` component](https://nextjs.org/docs/pages/api-reference/components/image).

The required images sizes are served on-the-fly via image proxy, and the whole magic works thanks to the following steps:

- The [`loader`](https://nextjs.org/docs/pages/api-reference/components/image#loader) prop setting for the `Image` component.
- The `nginx.conf` webserver configuration that forwards the image requests to the `imageResizer.php` script.
- The `imageResizer` script itself that is responsible for serving the images from the image proxy:
    - Locally and on the CI server, there is an [`imgProxy`](https://docs.imgproxy.net/) service running in a Docker container.
    - For production, image proxy provided by [VSH CDN](https://support.vshosting.cz/en/CDN/manipulating-images-in-cdn/) is used.

## Open Graph preset

Social networks need a JPG or PNG sharing image (`og:image`) of a known size, so `imageResizer.php` provides the `og` preset, e.g. `/content/images/product/123.png?preset=og`.
The URL is built by the `useSeo()` hook of the storefront, see [Open Graph](../model/seo-attributes.md#open-graph).

- the image is shrunk to fit into the 1200×630 px box recommended by Facebook, smaller images are kept as they are, nothing is cropped or padded; the `width`, `height` and `resize` parameters are ignored
- the format of the source image is kept regardless of the `Accept` header of the client (WebP is never returned), so the storefront uses the preset only for JPG and PNG images

!!! note

    With `CDN_RESIZE_DISABLE` set, the request is redirected to the original image, so neither the size nor the format is guaranteed.

## Usage

You can check official documentation for Next Image component [here](https://nextjs.org/docs/pages/api-reference/components/image). With this component we are able to use everything what the API provides us.

Purpose of this component is to add our custom loader. And add better error handling (in case of an error there is an empty image placeholder instead of broken image). Every other props are shared with Next Image component so feel free to use it as you wish.

## Code example

```tsx
yourComponent.tsx

import { Image } from 'components/Basic/Image/Image';
...

<div>
    <Image src={data.image?.url} alt={data.image?.name || data.name} width={300} height={300}/>
</div>

...
```
