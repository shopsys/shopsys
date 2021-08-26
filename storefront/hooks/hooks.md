### useGetWindowSize

-   This hook allows you to get access to the current height & width of the window object in real time. You can then use these values as dependecies of the useEffect hook to react to the given changes
-   The initial height and width are set to -1, as we have no access to the actual values during SSR. They have to be set to values that cannot occur on the client, that's why they are set to a negative value

### useResizeWidthEffect

-   This hook allows you to call a specific function when a breakpoint is crossed from either direction, or when the component's size is initialized in the client
-   The "breakpoint callbacks" are triggered only when the breakpoint is crossed
-   This is achieved by keeping the previous width in the state of the hook and comparing it to the new width and breakpoint
-   The initial width is set to -1, as we have no access to the actual value during SSR. It has to be set to a value that cannot occur on the client, that's why it is set to a negative value
-   This function takes in 3 - 5 arguments
    -   width (type number) = current width of the window object, can be optained from the useGetWindowSize hook
    -   breakpoint (type number) = if this breakpoint is crossed, the callbacks are triggered
    -   callbackWhenWider (type optional function) = callback which is triggered if the breakpoint is crossed in the upwards direction, meaning that the previous width was smaller than the breakpoint, and the current width is bigger
    -   callbackWhenNarrower (type optional function) = callback which is triggered if the breakpoint is crossed in the downwards direction, meaning that the previous width was bigger than the breakpoint, and the current width is smaller
    -   callbackWhenInitialized (type optional function) = callback which is triggered when the element's width is initialized in the client
