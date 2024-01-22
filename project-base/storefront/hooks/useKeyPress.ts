import { useEffect, useRef } from 'react';

export const useKeypress = (key: string, handler: () => void) => {
    const onKeyPressHandler = useRef<(event: KeyboardEvent) => void>();

    useEffect(() => {
        onKeyPressHandler.current = (event: KeyboardEvent) => {
            if (key === event.key) {
                handler();
            }
        };
    }, [key, handler]);

    useEffect(() => {
        const eventListener = (event: KeyboardEvent) => {
            onKeyPressHandler.current?.(event);
        };

        window.addEventListener('keydown', eventListener);

        return () => {
            window.removeEventListener('keydown', eventListener);
        };
    }, []);
};
