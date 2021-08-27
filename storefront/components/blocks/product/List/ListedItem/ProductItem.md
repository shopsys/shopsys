### Regular product with an image
```jsx padded
<ProductItem
    product={{
        detailSlug: 'televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova',
        name: 'Hello Kitty :)',
        flags: [{ name: 'sleva', rgbColor: '#ffffff' }],
        image: {
            url: '/images/styleguide/hello-kitty.jpg',
            width: 160,
            height: 160
        },
        price: {
            priceWithVat: 3499,
            priceWithoutVat: 2891.74,
            vatAmount: 607.26,
            isPriceFrom: false,
            currencyCode: 'CZK'
        },
        isMainVariant: false,
        availability: 'Skladem',
        availableStoresCountInformation: 'Můžete mít ihned na 1 prodejně',
        countExposedInStores: 'Můžete si prohlédnout na 1 prodejně'
    }}
/>
```
### Main variant without image
```jsx padded
<ProductItem
    product={{
        detailSlug: '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova',
        name: 'Hello Kitty :)',
        flags: [{ name: 'sleva', rgbColor: '#ffffff' }],
        image: null,
        price: {
            priceWithVat: 3499,
            priceWithoutVat: 2891.74,
            vatAmount: 607.26,
            isPriceFrom: false,
            currencyCode: 'CZK'
        },
        isMainVariant: true,
        availability: 'Skladem',
    }}
/>
```
