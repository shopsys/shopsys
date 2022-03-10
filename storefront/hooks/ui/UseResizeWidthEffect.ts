import { useEffect, useState } from 'react';

export const useResizeWidthEffect = (
    width: number,
    breakpoint: number,
    callbackWhenWider?: () => unknown,
    callbackWhenNarrower?: () => unknown,
    callbackWhenInitialized?: () => unknown,
): void => {
    const [previousWindowWidth, setPreviousWindowWidth] = useState(-1);

    useEffect(() => {
        if (callbackWhenInitialized !== undefined && previousWindowWidth === -1 && width !== -1) {
            setPreviousWindowWidth(width);
            callbackWhenInitialized();
        }
    }, [width, callbackWhenInitialized, previousWindowWidth]);

    useEffect(() => {
        if (
            callbackWhenNarrower !== undefined &&
            previousWindowWidth > breakpoint &&
            width <= breakpoint &&
            width !== -1
        ) {
            callbackWhenNarrower();
        }
        if (
            callbackWhenWider !== undefined &&
            previousWindowWidth <= breakpoint &&
            width > breakpoint &&
            width !== -1
        ) {
            callbackWhenWider();
        }
        setPreviousWindowWidth(width);
    }, [width, callbackWhenWider, callbackWhenNarrower, breakpoint, previousWindowWidth]);
};
