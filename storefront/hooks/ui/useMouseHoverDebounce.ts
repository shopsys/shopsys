import { useComponentUpdate } from 'hooks/helpers/useComponentUpdate';
import { debounce } from 'lodash';
import { useState } from 'react';

export const useMouseHoverDebounce = (onMouseEnter: boolean, onMouseLeave: boolean, delay = 300): boolean => {
    const [isItemHovered, setIsItemHovered] = useState(false);
    const [, setIsMouseEntered] = useState(false);

    const hideItemDebounce = debounce(() => {
        setIsMouseEntered((isMouseEntered) => {
            if (!isMouseEntered) {
                setIsItemHovered(false);
            }

            return false;
        });
    }, delay);

    useComponentUpdate(() => {
        setIsItemHovered(true);
        setIsMouseEntered(true);
    }, [onMouseEnter]);

    useComponentUpdate(() => {
        setIsMouseEntered(false);
        hideItemDebounce();
    }, [onMouseLeave]);

    return isItemHovered;
};
