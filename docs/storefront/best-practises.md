# Best practices

## Destructuring props

-   code clarity, easy way to set the default value

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

## Static constants above the component (using SCREAMING_CASE)

-   code clarity, it is not initialized every time a component is rendered

```ts
const TEST_IDENTIFIER = 'blocks-product-filter';

const Filter: FC<FilterProps> = ({ productFilterOptions, slug, formUpdateDependency }) => {
    ...
}
```

## \_\_typename in the GraphQL fragments

-   we use the `__typename` for business logic a lot in our codebase
-   there is a bug (or a behavior) in the URQl package that causes the `__typename` to be missing when it is read from the cache
-   to ensure that the `__typename` is always available, we add it to the fragments
-

## Don't use default exports and index files

-   improves DX thanks to better components' usage searchability

```tsx
export const MySuperComponent = () => {
    ...
}
```

## Don't spread props everywhere

-   spread only the props that are needed or destructure all props and use only the ones that are needed

wrong way:

```tsx
<LabelWrapper {...props}>
    <TextInputStyled {...props.fieldRef} {...props} />
</LabelWrapper>
```

good way:

```tsx
<LabelWrapper label={label} required={required}>
    <TextInputStyled type={type} variant={variant} {...props.fieldRef} />
</LabelWrapper>
```
