# Typescript

-   this codebase is written in [TypeScript](https://www.typescriptlang.org/)
-   the checks are set to strict mode in the tsconfig.json file.
-   strict flag enables tighter type checking, which on one hand brings stronger guarantees of code correctness but on the other hand it brings more overhead and requires familiarity with TypeScript development.
-   if you are not comfortable with TypeScript, you can set this option to false
-   Next.js automatically creates a next-env.d.ts file in the root directory, which cannot be moved, edited or deleted as it can break the application
-   you can check the official docs to find out how to use native [React](https://reactjs.org/docs/static-type-checking.html#typescript) or [Next.js](https://nextjs.org/docs/basic-features/typescript) features, such as hooks, SSR, SSG, etc. together with TypeScript
-   for other important packages which are used accross this codebase, check the docs below

## Creating React components with TypeScript

### Infering props

-   when writing components with TypeScript for compile-time checking and PropTypes for run-time checking, you can take advantage of the following type, which you can use to type the props object:

```typescript
function MyComponent( props: InferProps<typeof MyComponent.propTypes>){...}
```

-   this way you allow TypeScript to infer the props from the PropType definitions

### When the InferProps type is not enough

-   there will be situations in which this may not be enough (e.g. when passing native onClick events or style object containing CSS properties)
-   in cases like these, you can extend the props object with the following TypeScript syntax:

```typescript
function MyComponent(
    props: InferProps<typeof MyComponent.propTypes> & {
        onClick?: React.MouseEventHandler<HTMLButtonElement>;
        style?: CSSProperties;
        children: ReactNode;
    },
);
```

-   generally, every prop you would not explicitly define with PropTypes, you would define like this
-   in the code, you can see native props, where we define props in two separate rows. **The first one is for required props** and **the second one is for optional props**

```typescript
type NativeProps = ExtractNativePropsFromDefault<
    InputHTMLAttributes<HTMLInputElement>,
    'name' | 'id',
    'disabled' | 'style' | 'required'
>;
```

### Making props optional

-   then, if you want to make the prop optional, just provide a default value

```typescript
MyComponent.defaultProps = {
    name: 'John Doe',
};
```

### Custom enum-like props

-   sometimes, you will have an enum-like prop (e.g. size prop for button), for which you will have a couple of predefined values (let's say "large" and "small")
-   to make this work, you will have to type the oneOf generic function but also provide the values as arguments to the function itself:

```plain
size: PropTypes.oneOf<'large' | 'small'>(['large', 'small']).isRequired,
```

### No implicit default values

-   when you have a default case in a switch statement or if/else block which are dependent on a prop, you will need to provide an explicit "default" case together with the default prop value

```plain
...

switch(props.variant){
  case "default"
    Component = <MyComponentDefault />
  case "primary"
    Component = <MyComponentPrimary />
  case "secondary"
    Component = <MyComponentSecondary />
}

...

MyComponent.defaultProps = {
  variant: 'default'
}

...

MyComponent.propTypes{
  variant: PropTypes.oneOf<'default' | 'primary' | 'secondary'>(['default', 'primary', 'secondary']).isRequired
}
```

-   when using the component, you will not need to provide the prop value if you wish to go with the default case

```plain
<MyComponent /> (default)
<MyComponent variant="primary" /> (primary)
<MyComponent variant="secondary" /> (secondary)
```

### Passing props to component

-   you can easily pass props which have the same name using the spread operator, then specify the rest of the props explicitly

```plain
<input {...props} type="checkbox" />
```

## Creating forms with TypeScript and React Hook Form

-   when working with the React Hook Form package, you can use TypeScript to type the hooks and methods provided by the package

### Typing the useForm hook

-   you can pass in the types for the form fields using TypeScript types or interfaces

```plain
type FormFieldsTypes = {
  name: string;
  age: number;
}

...

const formProviderMethods = useForm<FormFieldsTypes>({...})
```

### Typing the SubmitHandler method

-   when working with submit handlers, you can specify both the method and the form fields

```plain
const formSubmitHandler: SubmitHandler<FormValues> = (formFields) => {...}
```
