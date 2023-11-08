# Image Component

UI component shows images served by the API with correct sizes on different devices.

## Components props

-   **image** - `ImageSizesFragmentApi` - nullable, property served from the API
-   **alt** - `string` - alternative text for image
-   **type** - `string` - size variant of image according to `images.yaml` (example)
-   **loading** - optional - HTML loading attribute for a specific image loading behavior (auto, lazy, eager)
-   **testId** - optional - string, used for testing

## Code example

```yaml
images.yaml
---
- name: transport
  class: Shopsys\FrameworkBundle\Model\Transport\Transport
  sizes:
      - name: ~ # size variant of image (should be passed to 'type' property) - "~" means "default"
        width: 35
        height: 20
        occurrence: 'Front-end: Ordering process'
        # additional sizes are used for responsive images in "source" tags in picture element
        # "media" should always be provided and contains valid media query
        additionalSizes:
            - { width: 70, height: 40, media: 'only screen and (-webkit-min-device-pixel-ratio: 1.5)' }
            - {
                  width: 90,
                  height: 50,
                  media: 'only screen and (min-width: 769px) and (-webkit-min-device-pixel-ratio: 1.5)',
              }
            - { width: 45, height: 25, media: '(min-width: 769px)' }
```

```tsx
yourComponent.tsx

import { Image } from 'components/Basic/Image/Image';
...

<div>
    <Image image={data.image} alt={data.name} />
</div>

...
```
