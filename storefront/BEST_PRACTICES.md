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

wrong way:
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

### __typename in the GraphQL fragments

- we use the `__typename` for business logic a lot in our codebase
- there is a bug (or a behavior) in the URQl package that causes the `__typename` to be missing when it is read from the cache
- to ensure that the `__typename` is always available, we add it to the fragments
- 
### Use classNames instead of props for styled-components

- improves styled components performance
- prevents complex logic in the styled component that hurts performance

```tsx
<SomethingStyled className='isActive' />
<SomethingDifferentStyled className={clsx(isActive && 'isActive', isDisabled && 'isDisabled')} />
```

### Don't use default exports and index files

- improves DX thanks to better components' usage searchability

```tsx
export const MySuperComponent = () => {
    ...
} 
```

### Don't use double literal syntax in styled-components

wrong way:
```tsx
export const MyComponentStyled = styled.div`
    ${({ theme }) => css`
        color: ${theme.color.white};
    `}
`;
```

good way:
```tsx
export const MyComponentStyled = styled.div(({ theme }) => css`
    color: ${theme.color.white};
`);
```

### Use "dollar props" if needed

- props to the styled-components that are not default elements' attribute should be prefixed with dollar sign to prevent this property to be written down to the DOM 

wrong way:
```tsx
<MySuperComponentStyled title="some title" variant="primary" />
```

good way:
```tsx
<MySuperComponentStyled title="some title" $variant="primary" />
```

### Don't spread props everywhere

- spread only the props that are needed or destructure all props and use only the ones that are needed

wrong way:
```tsx
<LabelWrapper {...props}>
    <TextInputStyled
        {...props.fieldRef}
        {...props}
    />
</LabelWrapper>
```

good way:
```tsx
<LabelWrapper label={label} required={required}>
    <TextInputStyled
        type={type}
        variant={variant}
        {...props.fieldRef}
    />
</LabelWrapper>
```
