import { mobileFirstSizes } from 'helpers/mediaQueries';
import { useEffect, useState } from 'react';

export const useMediaMin = (breakpoint: keyof typeof mobileFirstSizes, debounce = false): boolean | undefined => {
    const [match, setMatch] = useState<boolean>();

    useEffect(() => {
        const stateHandler = () => setMatch(window.innerWidth >= mobileFirstSizes[breakpoint]);

        let timer: number | undefined;
        const handler = () => {
            if (debounce) {
                window.clearTimeout(timer);
                timer = window.setTimeout(stateHandler, 200);
            } else {
                stateHandler();
            }
        };
        window.addEventListener('resize', handler);
        stateHandler();
        return () => {
            window.removeEventListener('resize', handler);
            window.clearTimeout(timer);
        };
    }, [breakpoint, debounce]);

    return match;
};
