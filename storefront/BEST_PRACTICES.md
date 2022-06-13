## Best practices for Storefront

### Destructuring props

- code clarity, easy way to set the default value

```ts
const RangeSlider: FC<RangeSliderProps> = ({
    min,
    max,
    delay = 300,
    minValue,
    maxValue,
    setMinValue,
    setMaxValue,
    dispatchMinValue,
    dispatchMaxValue,
}) => {
    ...
}
```

### Static constants above the component (using SCREAMING_CASE)

- code clarity, it is not initialized every time a component is rendered

```ts
const TEST_IDENTIFIER = 'blocks-product-filter';

const Filter: FC<FilterProps> = ({ productFilterOptions, slug, formUpdateDependency }) => {
    ...
}
```

### Handlers with parameters as double arrow functions

- code clarity, useCallback can be used to memoize the function

bad way:
```tsx
// no useCallback
const mySuperHandler = (id: number) => {
    // do something
};

// with useCallback
const mySuperHandler = useCallback((id: number) => {
    // do something
}, []);

<a onClick={() => mySuperHandler(1)}>Click me</a>
```

good way:
```tsx
// no useCallback
const mySuperHandler = (id: number) => () => {
    // do something
};

// with useCallback
const mySuperHandler = useCallback((id: number) => () => {
    // do something
}, []);

<a onClick={mySuperHandler(1)}>Click me</a>
```

