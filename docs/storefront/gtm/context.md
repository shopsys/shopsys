# GTM Context

If you need to handle a global context for your GTM handlers, you can use the given `GtmProvider` together with the `useGtmContext` hook. This approach was chosen as the most suitable way of providing a GTM context, as you can only wrap the given part of your application and can easily add or remove it from your app if not applicable

## GtmProvider

This provider, which is just a basic React context provider, can be used for maintaining a shared state between GTM events. If one event needs to depend on another one, this is a great place to synchronize them. This provider can then keep (and provide) the state based on which the events are synchronized

## useGtmContext

This hook is used for consuming the GTM context (inside the `GtmProvider`). Error is thrown if the hook is used outside of the provider.

## Usage example: Waiting with all view events until page view event has run

If you need to await some event (in this case the page view event) before you run some other asynchronous events, you can do so by storing a suitable state in your provider:

```ts
export type GtmContextType = {
    didPageViewRun: boolean;
    toggleDidPageViewRun: (newState: boolean) => void;
};
```

With this state structure, you then need to set and reset the value for `didPageViewRun`:

```ts
// set when page view runs (inside useGtmPageViewEvent.ts)
export const useGtmPageViewEvent = (gtmPageViewEvent: GtmPageViewEventType, areDataFetching?: boolean): void => {
    // skipped code
    const { setDidPageViewRun } = useGtmContext();

    useEffect(() => {
        if (gtmPageViewEvent._isLoaded && lastViewedSlug.current !== slug && !areDataFetching) {
            // skipped code
            setDidPageViewRun(true);
        }
    }, [gtmPageViewEvent, areDataFetching, slug]);
};

// reset inside the provider (inside GtmProvider.tsx)
useEffect(() => {
    const onRouteChangeStart = () => {
        setDidPageViewRun(false);
    };

    router.events.on('routeChangeStart', onRouteChangeStart);

    return () => {
        router.events.off('routeChangeStart', onRouteChangeStart);
    };
}, [router.events]);
```

Having this in place, you can now conditionally run subsequent view events (e.g. cart view event):

```ts
export const useGtmCartViewEvent = (gtmPageViewEvent: GtmPageViewEventType): void => {
    // skipped code
    const { didPageViewRun } = useGtmContext();

    useEffect(() => {
        if (
            didPageViewRun &&
            // skipped code
        ) {
            // skipped code - run the event
        }
    }, [/* skipped code */ didPageViewRun]);
};
```
