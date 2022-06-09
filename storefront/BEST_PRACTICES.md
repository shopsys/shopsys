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
