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

### usePagination

UsePagination hook builds upon useResizeWidthEffect by storing a boolean value to either hide or show mobile/desktop pagination. Another three arguments of usePagination are totalCount that you can get from your query, currentPage that you get from user state and pageSize that is set by default on 10.
Hook returns array of PaginationButtons that you map using your custom pagination button.

### useMouseHoverDebounce

-    This hook allows you to create a hover effect with delay on mouseLeave. Is really useful when you have some dropdown on hover and the body of the dropdown is absolutely positioned.
-    This function takes in 3 arguments
    -    onMouseEnter (type boolean) = this argument is just for trigger onMouseEnter function
    -    onMouseLeave (type boolean) = this argument is just for trigger onMouseLeave function
    -    delay (number by default set to 300) = if you want custom delay you can define this argument
-    This hook returns a boolean. True if your hover is active and false for hiding your hover.
