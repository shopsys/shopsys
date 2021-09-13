import { ForwardedRef, MutableRefObject, useEffect, useRef } from 'react';

export const useForwardedRef = (ref: ForwardedRef<any>): MutableRefObject<any> => {
    const innerRef = useRef(null);
    useEffect(() => {
        if (!ref) {
            return;
        }
        if (typeof ref === 'function') {
            ref(innerRef.current);
        } else {
            ref.current = innerRef.current;
        }
    });

    return innerRef;
};
