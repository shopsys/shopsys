# Web Vitals Improvements on Storefront

## 1 Reduce DOM size

This is probably the most time consuming part of our improvements. Math is pretty simple here. Bigger DOM size = higher blocking time (our worst metric). It also influences network traffic and from DX experience, it also brings more complex logic for work with styling. Besides one huge refactoring [pull request](https://github.com/shopsys/shopsys/pull/2749/commits) it was taken care of in every other touched task.

### 1.1 Remove hidden elements from DOM

We can basically say elements which don’t require animation and can be hidden/displayed instead of hidden className be removed from or attached to DOM. The perfect case is [refactoring filter groups](https://github.com/shopsys/shopsys/pull/2741).

On projects, they come most often in a closed state by default. Because of that, it is a perfect scenario for don’t attach them to the DOM in case they are collapsed.

```tsx
<FilterGroupWrapper>
    <FilterGroupTitle title={title} isOpen={isGroupOpen} onClick={() => setIsGroupOpen(!isGroupOpen)} />
    {isGroupOpen && (
        <FilterGroupContent>
            <RangeSlider
                min={minPriceOption}
                max={maxPriceOption}
                minValue={minimalPrice || minPriceOption}
                maxValue={maximalPrice || maxPriceOption}
                setMinValueCallback={setMinimalPrice}
                setMaxValueCallback={setMaximalPrice}
            />
        </FilterGroupContent>
    )}
</FilterGroupWrapper>
```

The same logic was applied to the main menu (a popup which is being displayed after hover in case when the menu has some child categories). Commit [here](https://github.com/shopsys/shopsys/pull/2749/commits/af75a2ebfe32a9f3725a9594005f54616cb27ffd).

### 1.2 Passing className instead adding element

This is also a pretty usual case. When we want to attach some styling to the used component, we add a wrapper over it (usually div) and to this element, we add the required styling. Instead, you can refactor this component to merge default classNames with classNames from props and you can pass classNames to this component instead of the wrapper.

```tsx
const CellHead: FC<CellProps> = ({ className, children, ...props }) => (
    <Cell className={twMergeCustom('font-bold text-greyLight', className)} isHead {...props}>
        {children}
    </Cell>
);
```

## 2 Accessibility

This is a pretty big topic, especially in our case, where we are not taking care of the Accessibility topic at all. But those subtopics for this metric can influence SEO as well as other metrics. Because of this, we took care of multiple issues:

### 2.1 Heading semantics

We were still using the mindset from a WYSIWYG approach where users used headings just for styling purposes (bigger font size or bolder text). This is very bad practice. Headings are not meant for styling purposes. Rather, they are supposed to be used to highlight the importance of the content. See [docs](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Heading_Elements).

### 2.2 Add titles, labels and alts

This can be split into several parts:

- **Inputs** - If the input doesn’t have any associated label element, it’s still necessary to explain this input to the screen readers. Either add a label element for this input or put an aria-label prop to the input element with the proper description
- **Links** and **Buttons** - In case there is no text inside the Link or Button component (usually clickable icons for which is the description icon itself), screen readers need to know about the purpose of the action. Add title prop to those elements.
- **Images** - add alt description to every img element

## 3. Dynamic imports

Components which are not immediately displayed but rather they are shown after a user action, those can be lazy loaded with the dynamic function from Next.js. In order to save some traffic and javascript execution time. This also helps a lot with the issue with unused javascript.

```tsx
const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Product/AddToCartPopup').then((component) => component.AddToCartPopup),
);
```

Then you can use the lazy loaded component as a regular component - most probably it will be included in some condition.

```tsx
return (
    <div>
        {componentContent}

        {!!popupData && <AddToCartPopup onCloseCallback={() => setPopupData(undefined)} addedCartItem={popupData} />}
    </div>
);
```

## 4. Images

### 4.1 Webp static images

From CDN, we should already get images in the format webP, but for static images, we don’t care much. Don’t forget to convert your images to webP format in order to get the best possible image optimization. Even better, use [image compression](https://tinypng.com/) afterwards as the next step.

### 4.2 Image specific width and height

Since the browser doesn’t know the dimensions of the image it can cause layout shifts (affects CLS - which is one of the most important metrics). Always specify width and height of the image. You can still adjust the size of the image by CSS.

```tsx
export const IconImage: FC<IconImageProps> = ({ icon, height, width, ...props }) => {
    return (
        <img
            src={`/icons/${icon}.png`}
            height={height !== undefined ? height : '24'}
            width={width !== undefined ? width : '24'}
            {...props}
        />
    );
};
```

## 5. Scripts

### 5.1 Load scripts only when they are used

In our Storefront, there was only one case of loading a third-party script when it was not yet being used. This is the Packetery script for one of the delivery options. This is a perfect example of this idea. Why load this script on every page when it is not needed? Move the scripts which are needed for some part of your application as close as possible. Our Packetery script was moved from the root \_app component (loaded on every page) to the second step of the cart (select delivery).

### 5.2 Load scripts with Script component

Next.js provides us with a [Script component](https://nextjs.org/docs/pages/building-your-application/optimizing/scripts), which is bundled with a straightforward API which provides us a better way of how we work with the scripts and optimization at the same time. The most used feature you will probably use is loading [strategy](https://nextjs.org/docs/pages/building-your-application/optimizing/scripts), which allows you to decide when a script should be loaded.

## 6. Refactor Icon component

This is not a generic improvement but specific to our project, where svg icons were used in a very bad way. We load all svg icons components and then select only one which we need. This is causing at least higher traffic.

In the [first part](https://github.com/shopsys/shopsys/pull/2722/files) of the refactoring, we refactored the Icon component to wrap the svg icon component, which is being passed to here through the prop.

In the [second part](https://github.com/shopsys/shopsys/pull/2745/files) of the refactoring, you can think about removing the Icon component completely and simply use directly the svg icon component where you need to use it.

## 7. Refactoring slider components

This refactoring was sort of experimental. It was about refactoring different kinds of sliders that we currently use.

One was refactoring the [SimpleNavigation](https://github.com/shopsys/shopsys/blob/14.0/project-base/storefront/components/Blocks/SimpleNavigation/SimpleNavigation.tsx) component, which is responsible for displaying slider on smaller viewports and regular list on regular and bigger viewports. This is used in several places where it is not required to control sliding behavior by buttons (just sliding on the screen). This was previously accomplished by the keen-slider library (Javascript solution). Now, it’s our own pure CSS solution.

Second was a more experimental solution for a specific use case. That place is [ProductSlider](https://github.com/shopsys/shopsys/blob/14.0/project-base/storefront/components/Blocks/Product/ProductsSlider.tsx). Which has also been freed from the keen-slider library and uses our simplified (yet limited) solution with a little bit of Javascript. This might not be an ideal solution for every project so we consider it as an alternative rather than replacement.

## 8. Refactor checkbox and radiobutton elements

As a styling solution for checkboxes and radiobuttons, we use a very old approach - images sprites - requiring a designer to paint their designs and then send it to the developer to implement them. It is one or more requests to download those images. Also, it has some delay in displaying the element itself (until an image is downloaded). Now, we have those elements designed with a [CSS solution](https://github.com/shopsys/shopsys/pull/2752).

## 9. Fix console errors and warnings

We all know how easy it is to get used to errors and warnings in the console, right? But remember, somebody put the log there for some reason. As it may seem like "It's working, so why care..." it can still lead to some performance issues and cause problems.

## 10. Diagnose server response time

Server timing diagnostics are disabled by default. To enable them, set `SERVER_TIMING=1` in the storefront server's environment (locally, in `project-base/storefront/.env.local`) and restart the storefront process. Set `SERVER_TIMING=0` or remove the variable and restart to disable them again. Only the exact value `1` enables diagnostics, including in production builds used on staging environments. This is a server-only setting; do not use the `NEXT_PUBLIC_` prefix.

Enable this temporarily on a controlled diagnostic environment: the response exposes internal GraphQL operation names and shared cache hit/miss status to visitors. Leave it disabled on public production environments unless this exposure is intentional. Disabling it skips the measuring GraphQL exchange and wrapper timing; data fetching and error propagation remain unchanged.

When enabled, open DevTools → Network, reload the page, and select the document request. Look for `Server-Timing` under Headers → Response Headers or inspect the Timing tab. The metrics describe this response only; they are not automatically stored or sent to Sentry or another monitoring service:

- `gssp`: total time spent in the `getServerSideProps` wrapper, including data fetching and preparing props. It does not include Next.js HTML rendering after the wrapper returns, proxy queues, or network latency.
- `redis_connect`: establishing the request's Redis connection.
- `translations`: loading the initial `common` translation namespace.
- `gql_<operation>`: elapsed time from starting a server-side GraphQL query to its final result, including authentication, cache lookup and backend work where applicable.
- `cache_<operation>`: Redis lookup duration, with `desc="hit"` or `desc="miss"`. Queries that bypass Redis do not emit this metric.

Metrics are local to each HTTP response and contain operation names, not variables, cache keys or customer data. Query durations overlap when requests run concurrently and must not be added together to calculate total SSR time. Cache timings are already included in the corresponding query timing.

Compare repeated requests for the same page, customer state and deployment. Use the slowest query and cache outcomes to narrow down a high TTFB before changing caching or deferring data. A cache hit still requires client-side parsing and rendering; it does not imply low JavaScript blocking time.
