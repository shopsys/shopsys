import { useGetWindowSize } from './useGetWindowSize';
import { RefObject, useCallback, useEffect, useState } from 'react';

export const useCalcElementHeight = (
    contentElement: RefObject<HTMLDivElement>,
    variable?: any,
): [number, () => void] => {
    const [contentElementHeight, setContentElementHeight] = useState(0);
    const { width } = useGetWindowSize();

    const calcHeight = useCallback(() => {
        if (contentElement.current && contentElementHeight !== contentElement.current.clientHeight) {
            setContentElementHeight(contentElement.current.clientHeight);
        }
    }, [contentElement, contentElementHeight]);

    useEffect(() => {
        calcHeight();
    }, [calcHeight, width, variable]);

    return [contentElementHeight, calcHeight];
};
