# Tailwind CSS

For styling purposes, we use the [Tailwind CSS](https://tailwindcss.com/) framework.

When it's better to use other solutions (add rules to a CSS file or include in inline `style`) we don't restrict you, and you can use what you think is the best for your case.

## Working with classNames

Sometimes, you need to implement a more complex logic than just putting a simple className string. Then we have two utilities: `twJoin` and `twMergeCustom` to help you solve this.

You can use `twJoin` in case you just want to split classNames into several groups for better readability.

```tsx
<div className={twJoin('w-8 text-red', 'hover:text-blue hover:scale-125')}>
  Hello world
</div>
```

You can use `twMergeCustom` if you need to conditionally render classNames or combine static classNames with classNames from the props. Here is a simplified example of a usual use-case.

```tsx
<div
  className={twMergeCustom(
    'text-black',
    isWithError && 'text-red',
    props.className
  )}
>
  Hello world
</div>
```

## Usage of Tailwind classes outside of className prop

Adding these lines to the user settings in your IDE is unnecessary but highly recommended.

```json
  "tailwindCSS.experimental.classRegex": [
    ["TwClass \\=([^;]*);", "'([^']*)'"],
    ["TwClass \\=([^;]*);", "\"([^\"]*)\""],
    ["TwClass \\=([^;]*);", "\\`([^\\`]*)\\`"]
  ]
```

This allows you to use Tailwind IntelliSense also in variables (not only in `className` props). The name of the variable needs to consist of the `TwClass` string (like `childrenTwClass`). It is useful for several cases:

- When you have to pass multiple classNames to a component. For example `className` for a wrapper and at the same time `className` for a child component in a loop.

```tsx
const simpleNavigationItemTwClass = 'lg:justify-center text-center'

<SimpleNavigation
  listedItems={readyCategorySeoMixLinks}
  className="mb-5"
  itemClassName={simpleNavigationItemTwClass}
/>
```

- You have multiple places where you need to use the same `className` or to export the variable.

```tsx
const sharedTwClass = 'lg:justify-center text-center'

<ComponentFirst className={sharedTwClass}>
<ComponentSecond className={sharedTwClass}>
```
