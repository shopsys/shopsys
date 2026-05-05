# GTM Context

If you need to handle a global context for your GTM handlers, you can use the given `GtmProvider` together with the `useGtmContext` hook. This approach was chosen as the most suitable way of providing a GTM context, as you can only wrap the given part of your application and can easily add or remove it from your app if not applicable

## GtmProvider

This provider, which is just a basic React context provider, can be used for maintaining a shared state between GTM events. If one event needs to depend on another one, this is a great place to synchronize them. This provider can then keep (and provide) the state based on which the events are synchronized

## useGtmContext

This hook is used for consuming the GTM context (inside the `GtmProvider`). Error is thrown if the hook is used outside of the provider.

## Usage example: Waiting with all view events until page ready event has run

If you need to await some event (in this case the page ready event) before you run some other asynchronous events, you can do so by storing a suitable state in your provider:

```ts
export type GtmContextType = {
    didPageReadyRun: boolean;
    setDidPageReadyRun: (newState: boolean) => void;
};
```

With this state structure, you then need to set and reset the value for `didPageReadyRun`:

```ts
// set when page ready runs (inside useGtmPageReadyEvent.ts)
export const useGtmPageReadyEvent = (gtmPageReadyEvent: GtmPageReadyEventType, areDataFetching?: boolean): void => {
    // skipped code
    const { setDidPageReadyRun } = useGtmContext();

    useEffect(() => {
        if (gtmPageReadyEvent._isLoaded && lastViewedSlug.current !== slug && !areDataFetching) {
            // skipped code
            setDidPageReadyRun(true);
        }
    }, [gtmPageReadyEvent, areDataFetching, slug]);
};

// reset inside the provider (inside GtmProvider.tsx)
useEffect(() => {
    const onRouteChangeStart = () => {
        setDidPageReadyRun(false);
    };

    router.events.on('routeChangeStart', onRouteChangeStart);

    return () => {
        router.events.off('routeChangeStart', onRouteChangeStart);
    };
}, [router.events]);
```

Having this in place, you can now conditionally run subsequent view events (e.g. cart view event):

```ts
export const useGtmCartViewEvent = (gtmPageReadyEvent: GtmPageReadyEventType): void => {
    // skipped code
    const { didPageReadyRun } = useGtmContext();

    useEffect(() => {
        if (
            didPageReadyRun &&
            // skipped code
        ) {
            // skipped code - run the event
        }
    }, [/* skipped code */ didPageReadyRun]);
};
```
