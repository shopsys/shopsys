# Tailwind CSS

For styling purposes we use [Tailwind CSS](https://tailwindcss.com/) framework.

When it's better to use another solution (add rule to CSS file or include inline `style`) we don't restrict you to use what you think is the best for your case.

## Working with classNames

Sometimes you need to implement more complex logic then just put simple className strings. Then we have two utilities `twJoin` and `twMergeCustom` to help you solve this.

`twJoin` you can use in case you just want to split classNames into several groups for better readability.

```tsx
<div className={twJoin("w-8 text-red", "hover:text-blue hover:scale-125")}>
    Hello world
</div>
```

`twMergeCustom` you use in case you would need to conditionally render classNames or even better case would be combining static classNames with classNames from the props. Here is a simplified usage of the usual use case.

```tsx
<div
    className={twMergeCustom(
        "text-black",
        isWithError && "text-red",
        props.className
    )}
>
    Hello world
</div>
```

## Usage of Tailwind classes outside of className prop

It's not necessary but highly recommended to add these lines to the user settings for your IDE.

```json
  "tailwindCSS.experimental.classRegex": [
    ["TwClass \\=([^;]*);", "'([^']*)'"],
    ["TwClass \\=([^;]*);", "\"([^\"]*)\""],
    ["TwClass \\=([^;]*);", "\\`([^\\`]*)\\`"]
  ]
```

This allows you to use Tailwind IntelliSense also in variables (not only in `className` props). Name of the variable needs to be consisted of the `TwClass` string (like `childrenTwClass`) It is useful for several cases:

-   Where you have to pass multiple classNames to the component. For example `className` for wrapper and at the same time `className` for child components in the loop.

```tsx
const simpleNavigationItemTwClass = 'lg:justify-center text-center'

<SimpleNavigation
  listedItems={readyCategorySeoMixLinks}
  className="mb-5"
  itemClassName={simpleNavigationItemTwClass}
/>
```

-   You have multiple places where you need to use the same `className` or to export the variable.

```tsx
const sharedTwClass = 'lg:justify-center text-center'

<ComponentFirst className={sharedTwClass}>
<ComponentSecond className={sharedTwClass}>
```
