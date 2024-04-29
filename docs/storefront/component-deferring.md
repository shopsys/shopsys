# Component deferring

To improve certain metrics (such as TBT), improve user experience, and make the Storefront application feel faster, we have implemented a custom deferring logic. This mechanism allows developers to delay rendering of certain parts of the application so that the initial render and hydration tasks are shorter. In essence, this mechanism is based on strategically setting timeouts and only rendering the desired content once the timesout finishes. In the meantime, the developer can provide a placeholder (component with a partial content and structure) or a skeleton (purely visual component used to show that something is loading).

In order to understand this mechanism and be able to work with it, read this documentation thoroughly. Only then will you be able to work with deferring efficiently.

## How to defer a component

In its simplest form, deferring a component is fairly easy and can be done quickly. You will need to do these steps:

1. **Add your page to deferred pages**

    To allow a component being deferred, you need to make sure that the page on which the component is located allows defers. By default, one component can be deferred on one page and not deferred on another. Therefore, let's say that you want to defer the category page, while product detail page is already deferred. Then, you have to make these changes inside `useDeferredRender`. You do not have to focus on the specific values within `CATEGORY_PAGE_DEFER_ORDER`, as we will discuss that in the next step.

    ```diff
    type DeferPage = 'product' | 'category' | 'homepage';

    type DeferPlace = (
        | typeof PRODUCT_PAGE_DEFER_ORDER
    +    | typeof CATEGORY_PAGE_DEFER_ORDER
    )[number];

    const getDeferPage = (router: NextRouter): DeferPage | 'non_deferred' => {
        if (router.pathname === FriendlyPagesDestinations.product) {
            return 'product';
    +    } else if (
    +        router.pathname === FriendlyPagesDestinations.category ||
    +        router.pathname === FriendlyPagesDestinations.seo_category
    +    ) {
    +       return 'category';
    +    }

        return 'non_deferred';
    };


    +const CATEGORY_PAGE_DEFER_ORDER = [
    +    'loaders',
    +    'footer',
    +    'product_list',
    +    'filter_panel',
    +    'sorting_bar',
    +    'last_visited',
    +    'autocomplete_search',
    +    'cart_in_header',
    +    'menu_iconic',
    +    'navigation',
    +    'mobile_menu',
    +    'newsletter',
    +    'user_consent',
    +    'gtm_head_script',
    +] as const;

    const deferConfigByPages = {
        product: PRODUCT_PAGE_DEFER_ORDER,
    +    category: CATEGORY_PAGE_DEFER_ORDER,
    };
    ```

2. **Set priority for the deferred place**

    The entire point of deferring a component is to make sure non-critical components load one-by-one, not all at the same time. For this, you have to specify the order in which components are deferred. Using the config below, you set the order of component defer (`loaders` are loaded first, `gtm_head_script` is loaded last). Mind that the time when the first component is loaded and how big are the gaps between components is set using `DEFER_START` and `DEFER_GAP`, which are discussed below.

    ```ts
    const CATEGORY_PAGE_DEFER_ORDER = [
        'loaders',
        'footer',
        'product_list',
        'filter_panel',
        'sorting_bar',
        'last_visited',
        'autocomplete_search',
        'cart_in_header',
        'menu_iconic',
        'navigation',
        'mobile_menu',
        'newsletter',
        'user_consent',
        'gtm_head_script',
    ] as const;
    ```

3. **Apply the defer to a given component**

    After you have set up your page to be deferred and defined all places in the desired order, you can now apply this at the given locations. For example, let's say you want to use the defer for the `autocomplete_search` location defined above. You will have to implement it this way.

    ```tsx
    import { SkeletonModuleAutocompleteSearch } from 'components/Blocks/Skeleton/SkeletonModuleAutocompleteSearch';
    import dynamic from 'next/dynamic';
    import { useDeferredRender } from 'utils/useDeferredRender';

    const AutocompleteSearch = dynamic(
        () => import('./AutocompleteSearch').then((component) => component.AutocompleteSearch),
        {
            ssr: false,
            loading: () => <SkeletonModuleAutocompleteSearch />,
        },
    );

    export const DeferredAutocompleteSearch: FC = () => {
        const shouldRender = useDeferredRender('autocomplete_search');

        return shouldRender ? <AutocompleteSearch /> : <SkeletonModuleAutocompleteSearch />;
    };
    ```

    You may notice that there are several things you have to handle:

    - get `shouldRender` from the `useDeferredRender` hook based on the predefined location
    - use `shouldRender` to conditionally show either skeleton/placeholder/null, or the actual component
    - import both the actual component and the substitute in a suitable way (this is discussed closely later in the cookbook)
    - create a wrapper with a prefix `Deferred` where you place the abovementioned logic

## How to correctly define a skeleton/placeholder component

There are 2 main stakeholders we need to think about when deferring a component; crawler bot and user. While a bot needs to see all the important links and information in the SSR HTML without caring about the looks of the UI, a user might not need all the information during the first couple of seconds, but the UI must look correct and nice, without big layout shifts. Because of that, we mainly work with 3 types of content that can be displayed as a substitute while the main things are loaded.

### Original content

If you both need to provide full information for crawlers and nice UI for users, it is usually the best to show the original content.

### Skeleton

A skeleton is a piece of HTML which tells the user that something is not ready yet. Its outline looks somewhat like the original content, but is much simpler. You should use this type of content for components which are not important for a bot (forms, buttons without links, etc.) but are visible in the UI right away, so the user needs to see something. They usually do not accept any props, as they are super simple.

### Placeholder

A placeholder is a piece of HTML containing the important information which has to be present for the bot. Its styles and structure are either as simple as possible when used for things which are not visible to users right away (footer and other things below the viewport), or might be almost identical to the main content for things visible to the user (navigation). They usually accept props with data to be displayed.

### No content (`return null`)

If you have content that is not visible to users during the initial render and also contains no important information for crawlers, it is the best to initially not render anything. This might be applied to places like user consent popup, last visited products, newsletter form, etc.

## How to correctly import deferred components

Based on the type of substitute content you need to use different way of importing the components.

### You initially use placeholder and content is in the viewport

In such situation, you can import your substitute component dynamically on SSR. Your main content must be imported right away. This is because of two things. Firstly, you cannot provide props to your loading component, so you cannot do anything like this:

```tsx
const MyComponent = dynamic(() => import('./MyComponent').then((component) => component.MyComponent), {
    loading: () => <MyComponentPlaceholder data={data} />,
});
```

And without this, if you would import the main component dynamically, there would be a fraction of a second when no content is displayed, which causes layout shifts. Because of that, you have to use the following syntax:

```tsx
import { MyComponent } from './MyComponent';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const MyComponentPlaceholder = dynamic(() =>
    import('./MyComponentPlaceholder').then((component) => component.MyComponentPlaceholder),
);

export const DeferredMyComponent: FC = () => {
    const [{ data }] = useDataQuery();
    const shouldRender = useDeferredRender('my_component');

    if (!data.myProperty) {
        return null;
    }

    return shouldRender ? <MyComponent data={data.myProperty} /> : <MyComponentPlaceholder data={data.myProperty} />;
};
```

### You initially use placeholder and content is not in the viewport

In this situation, you might ignore the problem with a layout shift mentioned in the previous scenario. Because you only care that the robot sees the information inside the HTML and that the user sees the nice UI after some time when he is expected to scroll down, you might import both components dynamically. You might even allow the main component to be only imported on the client and ignored on the server.

```tsx
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const MyComponentPlaceholder = dynamic(() =>
    import('./MyComponentPlaceholder').then((component) => component.MyComponentPlaceholder),
);

const MyComponent = dynamic(() => import('./MyComponent').then((component) => component.MyComponent), {
    ssr: false,
});

export const DeferredMyComponent: FC = () => {
    const [{ data }] = useDataQuery();
    const shouldRender = useDeferredRender('my_component');

    if (!data.myProperty) {
        return null;
    }

    return shouldRender ? <MyComponent data={data.myProperty} /> : <MyComponentPlaceholder data={data.myProperty} />;
};
```

### You initially use skeleton and content is in the viewport

Because a skeleton does not accept any props, you can use it as the loading component inside a dynamic import. This allows you to import both components dynamically, with the actual content only loaded on the client. With a loading component defined, there is not layout shift and the UI looks stable to the user.

```tsx
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const SkeletonMyComponent = dynamic(() =>
    import('./SkeletonMyComponent').then((component) => component.SkeletonMyComponent),
);

const MyComponent = dynamic(() => import('./MyComponent').then((component) => component.MyComponent), {
    ssr: false,
    loading: () => <SkeletonMyComponent />,
});

export const DeferredMyComponent: FC = () => {
    const [{ data }] = useDataQuery();
    const shouldRender = useDeferredRender('my_component');

    if (!data.myProperty) {
        return null;
    }

    return shouldRender ? <MyComponent data={data.myProperty} /> : <SkeletonMyComponent data={data.myProperty} />;
};
```

### You initially use skeleton and content is not in the viewport

In such case, you should not actually use a skeleton, as there is no reason for it. Rather do not display anything.

### You initially return `null`

If you initially return `null`, this means that you only have to care about the actual content, as there is no placeholder, nor a skeleton. In such case, you can import your actual content dynamically on the client, without the need for a loading component.

```tsx
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const MyComponent = dynamic(() => import('./MyComponent').then((component) => component.MyComponent), {
    ssr: false,
});

export const DeferredMyComponent: FC = () => {
    const [{ data }] = useDataQuery();
    const shouldRender = useDeferredRender('my_component');

    if (!data.myProperty) {
        return null;
    }

    return shouldRender ? <MyComponent data={data.myProperty} /> : null;
};
```

## How to wrap your page in the `PageDefer`

The deferring mechanism works the best if you wrap your page in `PageDefer`. This wrapper is essentially just a `Suspense` boundary (if deferring is turned on). If deferring is turned off, this wrapper simply returns the children components.

## How to set up defer start and defer gap

Inside `usDeferredRender.ts` there are two constants which control the strating point and gap of the defer mechanism:

-   `DEFER_START` tells the application how many miliseconds after the initial `useEffect` run the first deferred components should be rendered.
-   `DEFER_GAP` tells the application how large the gaps (in miliseconds) between subsequent deferred components should be.

Later defer means more time for the initial render and hydration, which might result in a less busy CPU thread, but it extends the time when the content is not ready for the user. Larger gaps mean more space for each render task, which might, again, result in a less busy CPU thread, but extends the time until all content is ready for the user.

## How to globally turn off defer mechanism

Inside `.env`, there is an environment variable `SHOULD_USE_DEFER`, which controls if the defer mechanism is turned on or off. By setting it to `0`, you turn of the _setTimeout_ logic and all content is loaded right away, but it has no effect on dynamic imports. This means, that if you import your component using the syntax below, it will still be only loaded on the client, which might take some time.

```tsx
const MyComponent = dynamic(() => import('./MyComponent').then((component) => component.MyComponent), {
    ssr: false,
});
```
